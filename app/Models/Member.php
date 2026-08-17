<?php

class Member extends Model
{
    protected $fillable = [
        'full_name', 'position_en', 'position_es', 'image_path',
        'socials', 'active', 'is_honor', 'graduation_year', 'honor_quote',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_honor' => 'boolean',
    ];

    private const ALLOWED_SOCIAL_HOSTS = ['linkedin.com', 'github.com', 'twitter.com', 'instagram.com', 'x.com'];

    public function safeSocialUrl(): string
    {
        $host = parse_url((string) $this->socials, PHP_URL_HOST);
        if (!$host) return '#';

        foreach (self::ALLOWED_SOCIAL_HOSTS as $allowed) {
            if (str_ends_with($host, $allowed)) return $this->socials;
        }
        return '#';
    }

    public function scopeActiveMembers($query)
    {
        return $query->where('active', true)->where('is_honor', false);
    }

    public function scopeHonorMembers($query)
    {
        return $query->where('is_honor', true);
    }

    public function scopePastMembers($query)
    {
        return $query->where('active', false)->where('is_honor', false);
    }
}