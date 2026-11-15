<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\GuestRequest;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard guests tab, the Laravel version of the legacy guests/guests_list.php:
 * external guests who can log in to see the attendees of the events they have access to.
 */
class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $guests = Guest::query()
            ->with(['events' => fn ($query) => $query->select(['events.id', 'title_es'])->orderByDesc('start_datetime')])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.guests.index', [
            'guests' => $guests,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.guests.form', [
            'guest' => new Guest(['is_active' => true]),
            'events' => $this->selectableEvents(),
            'selectedEventIds' => [],
        ]);
    }

    public function store(GuestRequest $request): RedirectResponse
    {
        $guest = DB::transaction(function () use ($request): Guest {
            $guest = Guest::create($request->guestAttributes());
            $guest->events()->sync($request->eventIds());

            return $guest;
        });

        return to_route('dashboard.guests.index')
            ->with('status', __('Invitado «:username» creado.', ['username' => $guest->username]));
    }

    public function edit(Guest $guest): View
    {
        return view('dashboard.guests.form', [
            'guest' => $guest,
            'events' => $this->selectableEvents(),
            'selectedEventIds' => $guest->events()->pluck('events.id')->all(),
        ]);
    }

    public function update(GuestRequest $request, Guest $guest): RedirectResponse
    {
        DB::transaction(function () use ($request, $guest): void {
            $guest->update($request->guestAttributes());
            $guest->events()->sync($request->eventIds());
        });

        return to_route('dashboard.guests.index')
            ->with('status', __('Invitado «:username» actualizado.', ['username' => $guest->username]));
    }

    /**
     * Event access rows are removed by the foreign key cascade (and explicitly, for legacy tables without it).
     */
    public function destroy(Guest $guest): RedirectResponse
    {
        $username = $guest->username;

        DB::transaction(function () use ($guest): void {
            $guest->events()->detach();
            $guest->delete();
        });

        return to_route('dashboard.guests.index')
            ->with('status', __('Invitado «:username» eliminado.', ['username' => $username]));
    }

    /**
     * @return Collection<int, Event>
     */
    private function selectableEvents(): Collection
    {
        return Event::query()
            ->select(['id', 'title_es', 'start_datetime'])
            ->orderByDesc('start_datetime')
            ->get();
    }
}
