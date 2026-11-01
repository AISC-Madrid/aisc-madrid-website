<?php

namespace App\Models;

use Database\Factories\RecruitingApplicantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Applications to join the association, one per email and recruiting cohort ("2026-27-1C").
 *
 * @property int $id
 * @property string $full_name
 * @property string $email
 * @property string|null $campus
 * @property string $position
 * @property string $interest
 * @property string $cohort
 * @property Carbon|null $submitted_at
 */
class RecruitingApplicant extends Model
{
    /** @use HasFactory<RecruitingApplicantFactory> */
    use HasFactory;

    protected $table = 'recruiting';

    const CREATED_AT = 'submitted_at';

    const UPDATED_AT = null;

    protected $fillable = [
        'full_name',
        'email',
        'campus',
        'position',
        'interest',
        'cohort',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * @param  Builder<RecruitingApplicant>  $query
     * @return Builder<RecruitingApplicant>
     */
    public function scopeForCohort(Builder $query, string $cohort): Builder
    {
        return $query->where('cohort', $cohort);
    }

    /**
     * Applicants of the cohort set in config('aisc.recruiting_cohort').
     *
     * @param  Builder<RecruitingApplicant>  $query
     * @return Builder<RecruitingApplicant>
     */
    public function scopeCurrentCohort(Builder $query): Builder
    {
        return $query->forCohort((string) config('aisc.recruiting_cohort'));
    }
}
