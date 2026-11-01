<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Events use the legacy schema: the type is free text in type_es / type_en and the speaker
 * is free text in speaker / company.
 *
 * @property int $id
 * @property string|null $type_es
 * @property string|null $type_en
 * @property string $speaker
 * @property string|null $company
 * @property-read string|null $image_url
 * @property-read array<int, string> $gallery_urls
 * @property-read string $type_slug
 * @property-read string|null $speaker_label
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory, ResolvesMediaUrls;

    protected $table = 'events';

    protected $fillable = [
        'title_es',
        'title_en',
        'type_es',
        'type_en',
        'speaker',
        'company',
        'description_es',
        'description_en',
        'start_datetime',
        'end_datetime',
        'location',
        'image_path',
        'gallery_paths',
        'google_calendar_url',
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
        'reminder_days_before' => 'integer',
    ];

    /**
     * @return BelongsToMany<Speaker, $this, EventSpeaker>
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(
            Speaker::class,
            'event_speakers',
            'event_id',
            'speaker_id'
        )->using(EventSpeaker::class)
            ->withPivot('role', 'sort_order')
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<EventRegistration, $this>
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Type name in the given locale (type_es / type_en).
     */
    public function typeName(string $locale): string
    {
        return trim((string) ($locale === 'en' ? $this->type_en : $this->type_es));
    }

    /**
     * Lower-case English type used by the events filter ("event", "workshop", ...), like the legacy site.
     *
     * @return Attribute<string, never>
     */
    protected function typeSlug(): Attribute
    {
        return Attribute::make(get: fn (): string => Str::lower(trim((string) $this->type_en)));
    }

    /**
     * Speaker with the company when there is one, e.g. "Ada Lovelace (Analytical Engines)".
     *
     * @return Attribute<string|null, never>
     */
    protected function speakerLabel(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $speaker = trim((string) $this->speaker);

            if ($speaker === '') {
                return null;
            }

            $company = trim((string) $this->company);

            return $company === '' ? $speaker : "{$speaker} ({$company})";
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(get: fn (): ?string => self::mediaUrl($this->image_path));
    }

    /**
     * @return Attribute<array<int, string>, never>
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::make(get: fn (): array => collect($this->gallery_paths ?? [])
            ->map(fn (mixed $path): ?string => is_string($path) ? self::mediaUrl($path) : null)
            ->filter()
            ->values()
            ->all());
    }
}
