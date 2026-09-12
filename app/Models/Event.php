<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title_es',
        'title_en',
        'type_id',
        'description_es',
        'description_en',
        'start_datetime',
        'end_datetime',
        'location',
        'image_path',
        'gallery_paths',
        'youtube_url',
        'requires_registration',
        'reminder_enabled',
        'reminder_days_before',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'gallery_paths' => 'array',
        'requires_registration' => 'boolean',
        'reminder_enabled' => 'boolean',
    ];

    /**
     * @return BelongsTo<EventType, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'type_id');
    }

    /**
     * @return BelongsToMany<Speaker, $this>
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(
            Speaker::class,
            'event_speakers',
            'event_id',
            'speaker_id'
        )->withPivot('role', 'sort_order')
            ->orderBy('sort_order');
    }
}
