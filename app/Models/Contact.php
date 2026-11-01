<?php

namespace App\Models;

use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    /**
     * @return HasMany<ContactEmailLog, $this>
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(ContactEmailLog::class);
    }

    /**
     * Name used in email greetings: greeting_name, or else the first word of full_name.
     */
    public function greetingName(): string
    {
        return $this->greeting_name ?: Str::before(trim($this->full_name), ' ');
    }
}
