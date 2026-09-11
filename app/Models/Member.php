<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
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
        'honor_member',
        'graduation_year',
        'honor_quote',
        'image_path',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'board' => 'boolean',
        'active' => 'boolean',
        'honor_member' => 'boolean',
    ];

    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class, 'member_id');
    }

    public function scopeActiveMembers(Builder $query): Builder
    {
        return $query->where('active', true)->where('honor_member', false);
    }

    public function scopeHonorMembers(Builder $query): Builder
    {
        return $query->where('honor_member', true);
    }

    public function safeSocialUrl(): string
    {
        if (! $this->socials || ! filter_var($this->socials, FILTER_VALIDATE_URL)) {
            return '#';
        }

        return $this->socials;
    }
}
