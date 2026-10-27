<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string|null $image_url
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
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'tags' => 'array',
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
}
