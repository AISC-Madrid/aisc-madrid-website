<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Links an event with a speaker, with their role ("ponente", "moderador") and display order.
 *
 * @property int $event_id
 * @property int $speaker_id
 * @property string|null $role
 * @property int $sort_order
 */
class EventSpeaker extends Pivot
{
    protected $table = 'event_speakers';

    public $timestamps = false;

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
