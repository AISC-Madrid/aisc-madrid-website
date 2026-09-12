<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    protected $table = 'guests';

    const UPDATED_AT = null;

    protected $fillable = [
        'username',
        'password_hash',
        'name',
        'email',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return HasOne<Speaker, $this>
     */
    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class, 'guest_id');
    }
}
