<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FormSubmission;
use App\Models\Member;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        $registrationsByEvent = $this->registrationsByEvent($upcomingEvents->pluck('id'));

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
                'totalRegistrations' => $this->countIfTableExists('event_registrations'),
            ],
            'recruiting' => [
                'cohort' => config('aisc.recruiting_cohort'),
                'applicants' => $this->countIfTableExists(
                    'recruiting',
                    ['cohort' => config('aisc.recruiting_cohort')],
                ),
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

    /**
     * Registrations per upcoming event, keyed by event id. Empty when the legacy table is not there.
     *
     * @param  Collection<int, int>  $eventIds
     * @return Collection<int, int>
     */
    private function registrationsByEvent(Collection $eventIds): Collection
    {
        if ($eventIds->isEmpty() || ! Schema::hasTable('event_registrations')) {
            return collect();
        }

        return DB::table('event_registrations')
            ->selectRaw('event_id, COUNT(*) as total')
            ->whereIn('event_id', $eventIds)
            ->groupBy('event_id')
            ->pluck('total', 'event_id')
            ->map(fn ($total): int => (int) $total);
    }

    /**
     * Count rows in a legacy table that has no model yet (event_registrations, recruiting).
     * Returns null when the table does not exist, so the dashboard can hide that number.
     *
     * @param  array<string, mixed>  $where
     */
    private function countIfTableExists(string $table, array $where = []): ?int
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        return DB::table($table)->where($where)->count();
    }
}
