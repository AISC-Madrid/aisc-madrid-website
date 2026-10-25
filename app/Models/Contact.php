<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasFactory;

    protected $table = 'contacts';

    const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'greeting_name',
        'email',
        'category',
        'organization',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function emailLogs(): HasMany
    {
        return $this->hasMany(ContactEmailLog::class);
    }

    public function greetingName(): string
    {
        return $this->greeting_name ?: strtok($this->full_name, ' ');
    }
}
