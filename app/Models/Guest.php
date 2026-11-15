<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * External guest (speaker, company…) with a login to see the attendees of the events it has access to.
 *
 * @property int $id
 * @property string $username
 * @property string|null $name
 * @property string|null $email
 * @property bool|null $is_active
 * @property Carbon|null $created_at
 */
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

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
        'created_at' => 'datetime',
    ];

    /**
     * @return HasOne<Speaker, $this>
     */
    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class, 'guest_id');
    }

    /**
     * Events this guest can see, through the event_guest_access table.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_guest_access');
    }
}
