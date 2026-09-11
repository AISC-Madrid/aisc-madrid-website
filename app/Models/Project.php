<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

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
}
