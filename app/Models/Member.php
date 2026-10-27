<?php

namespace App\Models;

use App\Casts\YesNoBoolean;
use App\Enums\MemberRole;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $full_name
 * @property string $password_hash
 * @property MemberRole|null $role
 * @property string|null $mail
 * @property-read string $name
 * @property-read string|null $email
 */
class Member extends Authenticatable
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $table = 'members';

    public $timestamps = false;

    /**
     * The legacy members table has no remember_token column, so "remember me" is a no-op.
     *
     * @var string
     */
    protected $rememberTokenName = '';

    protected $fillable = [
        'full_name',
        'password_hash',
        'role',
        'mail',
        'position_es',
        'position_en',
        'phone',
        'dni',
        'socials',
        'board',
        'active',
        'image_path',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'role' => MemberRole::class,
        'board' => YesNoBoolean::class,
        'active' => YesNoBoolean::class,
    ];

    /**
     * @return HasOne<Speaker, $this>
     */
    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class, 'member_id');
    }

    /**
     * @return HasOne<AlumniHonor, $this>
     */
    public function alumniHonor(): HasOne
    {
        return $this->hasOne(AlumniHonor::class, 'member_id');
    }

    /**
     * @param  Builder<Member>  $query
     * @return Builder<Member>
     */
    public function scopeActiveMembers(Builder $query): Builder
    {
        return $query->where('active', 'yes')->whereDoesntHave('alumniHonor');
    }

    /**
     * @param  Builder<Member>  $query
     * @return Builder<Member>
     */
    public function scopeHonorMembers(Builder $query): Builder
    {
        return $query->whereHas('alumniHonor');
    }

    /**
     * Passwords live in the legacy password_hash column (bcrypt from PHP's password_hash()).
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * @param  array<int, MemberRole|string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        if ($this->role === null) {
            return false;
        }

        $roleValues = array_map(
            fn (MemberRole|string $role): string => $role instanceof MemberRole ? $role->value : $role,
            $roles,
        );

        return in_array($this->role->value, $roleValues, true);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => $this->full_name ?? '');
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function email(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->mail);
    }

    /**
     * Get the member's initials for the sidebar avatar.
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function safeSocialUrl(): string
    {
        if (! $this->socials || ! filter_var($this->socials, FILTER_VALIDATE_URL)) {
            return '#';
        }

        return $this->socials;
    }
}
