<?php

namespace App\Models;

use Database\Factories\EventRegistrationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * People signed up for an event. The legacy site stores attendance as free text:
 * "attended" or "not_attended".
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property string $email
 * @property Carbon $registration_date
 * @property string|null $attendance_status
 * @property bool $qr_email_sent
 * @property bool $reminder_sent
 */
class EventRegistration extends Model
{
    /** @use HasFactory<EventRegistrationFactory> */
    use HasFactory;

    public const STATUS_ATTENDED = 'attended';

    public const STATUS_NOT_ATTENDED = 'not_attended';

    protected $table = 'event_registrations';

    const CREATED_AT = 'registration_date';

    const UPDATED_AT = null;

    protected $fillable = [
        'event_id',
        'name',
        'email',
        'attendance_status',
        'qr_email_sent',
        'reminder_sent',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'qr_email_sent' => 'boolean',
        'reminder_sent' => 'boolean',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hasAttended(): bool
    {
        return $this->attendance_status === self::STATUS_ATTENDED;
    }

    /**
     * @param  Builder<EventRegistration>  $query
     * @return Builder<EventRegistration>
     */
    public function scopeAttended(Builder $query): Builder
    {
        return $query->where('attendance_status', self::STATUS_ATTENDED);
    }

    /**
     * Number of registrations per event, keyed by event id. Events without registrations are left out.
     *
     * @param  Collection<int, int>  $eventIds
     * @return Collection<int, int>
     */
    public static function countsByEvent(Collection $eventIds): Collection
    {
        if ($eventIds->isEmpty()) {
            return collect();
        }

        return self::query()
            ->selectRaw('event_id, COUNT(*) as total')
            ->whereIn('event_id', $eventIds)
            ->groupBy('event_id')
            ->pluck('total', 'event_id')
            ->map(fn ($total): int => (int) $total)
            ->toBase();
    }
}
