<?php

use App\Models\RecruitingApplicant;
use Illuminate\Database\UniqueConstraintViolationException;

test('a recruiting applicant can be created with factory', function () {
    $applicant = RecruitingApplicant::factory()->create();

    expect($applicant->exists)->toBeTrue()
        ->and($applicant->getTable())->toBe('recruiting')
        ->and($applicant->submitted_at)->not->toBeNull();
});

test('applicants can be filtered by the current cohort', function () {
    config(['aisc.recruiting_cohort' => '2026-27-1C']);
    RecruitingApplicant::factory()->count(2)->forCohort('2026-27-1C')->create();
    RecruitingApplicant::factory()->forCohort('2025-26-2C')->create();

    expect(RecruitingApplicant::currentCohort()->count())->toBe(2)
        ->and(RecruitingApplicant::forCohort('2025-26-2C')->count())->toBe(1);
});

test('the same email can apply once per cohort', function () {
    RecruitingApplicant::factory()->forCohort('2026-27-1C')->create(['email' => 'ada@example.com']);
    RecruitingApplicant::factory()->forCohort('2025-26-2C')->create(['email' => 'ada@example.com']);

    expect(fn () => RecruitingApplicant::factory()->forCohort('2026-27-1C')->create(['email' => 'ada@example.com']))
        ->toThrow(UniqueConstraintViolationException::class);
});
