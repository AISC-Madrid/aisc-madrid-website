<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $table = 'speakers';

    const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'organization',
        'linkedin_url',
        'member_id',
        'guest_id',
    ];

    /**
     * @return BelongsToMany<Event, $this>
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            Event::class,
            'event_speakers',
            'speaker_id',
            'event_id'
        )
            ->withPivot('role', 'sort_order')
            ->orderBy('sort_order');
    }

    /**
     * @return BelongsTo<Member, $this>
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    /**
     * @return BelongsTo<Guest, $this>
     */
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }
}
