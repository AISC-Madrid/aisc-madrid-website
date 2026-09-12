<?php

namespace App\Models;

use App\Casts\YesNoBoolean;
use App\Enums\MemberRole;
use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    protected $table = 'members';

    public $timestamps = false;

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
     * @param Builder<Member> $query
     * @return Builder<Member>
     */
    public function scopeActiveMembers(Builder $query): Builder
    {
        return $query->where('active', 'yes')->whereDoesntHave('alumniHonor');
    }

    /**
     * @param Builder<Member> $query
     * @return Builder<Member>
     */
    public function scopeHonorMembers(Builder $query): Builder
    {
        return $query->whereHas('alumniHonor');
    }

    public function safeSocialUrl(): string
    {
        if (! $this->socials || ! filter_var($this->socials, FILTER_VALIDATE_URL)) {
            return '#';
        }

        return $this->socials;
    }
}
