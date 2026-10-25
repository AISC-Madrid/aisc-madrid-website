<?php

namespace App\Models;

use Database\Factories\EventGuestAccessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventGuestAccess extends Model
{
    /** @use HasFactory<EventGuestAccessFactory> */
    use HasFactory;

    protected $table = 'event_guest_access';

    public $timestamps = false;

    protected $fillable = [
        'guest_id',
        'event_id',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
