<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
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

    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class, 'member_id');
    }
}