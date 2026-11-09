<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string|null $image_url
 * @property-read array<int, string> $gallery_urls
 * @property-read string $phase
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, ResolvesMediaUrls;

    protected $fillable = [
        'title_es',
        'title_en',
        'category',
        'description_es',
        'description_en',
        'tags',
        'team_credit',
        'github_url',
        'external_url',
        'image_path',
        'gallery_paths',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'tags' => 'array',
        'gallery_paths' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * @return Attribute<string|null, never>
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => self::mediaUrl($this->image_path));
    }

    /**
     * @return Attribute<array<int, string>, never>
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(fn (): array => collect($this->gallery_paths ?? [])
            ->map(fn (string $path): ?string => self::mediaUrl($path))
            ->filter()
            ->values()
            ->all());
    }

    /**
     * upcoming | ongoing | finished, derived from the dates.
     *
     * @return Attribute<string, never>
     */
    protected function phase(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->start_date?->isFuture()) {
                return 'upcoming';
            }

            if ($this->end_date !== null && $this->end_date->isPast() && ! $this->end_date->isToday()) {
                return 'finished';
            }

            return 'ongoing';
        });
    }
}
