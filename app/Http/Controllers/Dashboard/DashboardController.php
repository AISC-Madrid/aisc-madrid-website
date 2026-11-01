<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\FormSubmission;
use App\Models\Member;
use App\Models\Project;
use App\Models\RecruitingApplicant;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/**
 * Overview of the website, like the legacy dashboard/dashboard.php: sign-ups and newsletter,
 * events and registrations, recruiting applicants and the latest subscribers.
 */
class DashboardController extends Controller
{
    private const MONTHS_IN_CHART = 12;

    public function index(): View
    {
        $now = CarbonImmutable::now();

        $totalUsers = FormSubmission::count();
        $newsletterSubscribers = FormSubmission::where('newsletter', 'yes')->count();

        $upcomingEvents = Event::query()
            ->where('start_datetime', '>', $now)
            ->orderBy('start_datetime')
            ->get(['id', 'title_es', 'title_en', 'type_es', 'type_en', 'start_datetime', 'end_datetime', 'location']);

        $registrationsByEvent = EventRegistration::countsByEvent($upcomingEvents->pluck('id'));

        return view('dashboard', [
            'users' => [
                'total' => $totalUsers,
                'newLast30Days' => FormSubmission::where('submitted_at', '>=', $now->subDays(30))->count(),
                'newsletter' => $newsletterSubscribers,
                'unsubscribed' => $totalUsers - $newsletterSubscribers,
                'newsletterRate' => $totalUsers > 0 ? (int) round($newsletterSubscribers / $totalUsers * 100) : 0,
            ],
            'events' => [
                'upcoming' => $upcomingEvents->count(),
                'past' => Event::where('end_datetime', '<', $now)->count(),
                'totalRegistrations' => EventRegistration::count(),
            ],
            'recruiting' => [
                'cohort' => config('aisc.recruiting_cohort'),
                'applicants' => RecruitingApplicant::currentCohort()->count(),
            ],
            'community' => [
                'activeMembers' => Member::activeMembers()->count(),
                'boardMembers' => Member::activeMembers()->where('board', 'yes')->count(),
                'projects' => Project::count(),
            ],
            'signupsByMonth' => $this->signupsByMonth($now),
            'latestSubscribers' => FormSubmission::query()
                ->select(['id', 'full_name', 'email', 'newsletter', 'submitted_at'])
                ->orderByDesc('submitted_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'nextEvents' => $upcomingEvents->take(5),
            'registrationsByEvent' => $registrationsByEvent,
        ]);
    }

    /**
     * Sign-ups per month for the last 12 months, oldest first. Grouped in PHP so it works on MySQL and SQLite.
     *
     * @return Collection<int, array{month: CarbonImmutable, total: int}>
     */
    private function signupsByMonth(CarbonImmutable $now): Collection
    {
        $firstMonth = $now->startOfMonth()->subMonths(self::MONTHS_IN_CHART - 1);

        $totalsByMonth = FormSubmission::query()
            ->where('submitted_at', '>=', $firstMonth)
            ->pluck('submitted_at')
            ->countBy(fn ($submittedAt): string => $submittedAt->format('Y-m'));

        return collect(range(0, self::MONTHS_IN_CHART - 1))->map(function (int $offset) use ($firstMonth, $totalsByMonth): array {
            $month = $firstMonth->addMonths($offset);

            return [
                'month' => $month,
                'total' => (int) $totalsByMonth->get($month->format('Y-m'), 0),
            ];
        });
    }
}
