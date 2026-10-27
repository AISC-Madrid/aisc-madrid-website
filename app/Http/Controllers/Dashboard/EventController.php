<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\BuildAttendeesPdf;
use App\Actions\StoreWebpImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\EventRequest;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Dashboard events tab, the Laravel version of the legacy events/events_list.php:
 * list every event, and (admin / events roles) create, edit, delete and export attendees as PDF.
 */
class EventController extends Controller
{
    public function __construct(private StoreWebpImage $storeWebpImage) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $events = Event::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title_es', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%");
            }))
            ->orderByDesc('start_datetime')
            ->paginate(25)
            ->withQueryString();

        $member = $request->user();

        return view('dashboard.events.index', [
            'events' => $events,
            'search' => $search,
            'registrationCounts' => $this->registrationCounts(collect($events->items())->pluck('id')),
            'canManage' => $member instanceof Member && $member->hasAnyRole(['admin', 'events']),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.events.form', ['event' => new Event([
            'speaker' => 'AISC Madrid',
            'reminder_days_before' => 2,
        ])]);
    }

    public function store(EventRequest $request): RedirectResponse
    {
        try {
            // If an upload fails the transaction rolls back, so no half-created event is left behind.
            $event = DB::transaction(function () use ($request): Event {
                $event = Event::create($request->eventAttributes());

                $event->update([
                    'image_path' => ($this->storeWebpImage)($request->file('image'), $this->folderFor($event)),
                    'gallery_paths' => $this->storeGallery($request, $event),
                ]);

                return $event;
            });
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        return to_route('dashboard.events.index')
            ->with('status', __('Evento «:title» creado.', ['title' => $event->title_es]));
    }

    public function edit(Event $event): View
    {
        return view('dashboard.events.form', ['event' => $event]);
    }

    /**
     * New main image replaces the old one; new gallery photos replace the whole gallery (legacy behaviour).
     */
    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $attributes = $request->eventAttributes();

        try {
            if ($request->hasFile('image')) {
                $attributes['image_path'] = ($this->storeWebpImage)($request->file('image'), $this->folderFor($event));
            }

            if ($request->hasFile('gallery')) {
                $attributes['gallery_paths'] = $this->storeGallery($request, $event);
            }
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        $event->update($attributes);

        return to_route('dashboard.events.index')
            ->with('status', __('Evento «:title» actualizado.', ['title' => $event->title_es]));
    }

    /**
     * Deletes the event row. Like the legacy page, its images stay in the bucket.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $title = $event->title_es;

        $event->delete();

        return to_route('dashboard.events.index')
            ->with('status', __('Evento «:title» eliminado.', ['title' => $title]));
    }

    public function attendeesPdf(Event $event, BuildAttendeesPdf $buildAttendeesPdf): Response
    {
        $registrations = Schema::hasTable('event_registrations')
            ? DB::table('event_registrations')
                ->select(['name', 'email', 'attendance_status'])
                ->where('event_id', $event->id)
                ->orderBy('name')
                ->get()
            : collect();

        $filename = 'asistentes-'.$event->id.'-'.Str::slug($event->title_es).'.pdf';

        return response($buildAttendeesPdf($event, $registrations), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    /**
     * Saving the event or its images failed: log the full error and send the member back to the
     * form (with what they typed) instead of a 500 page. Only admin / events members reach this,
     * so a short technical reason is shown to help diagnose problems on the server.
     */
    private function uploadFailed(Throwable $exception): RedirectResponse
    {
        report($exception);

        $reason = class_basename($exception).': '.Str::limit($exception->getMessage(), 220);

        return back()
            ->withInput()
            ->withErrors(['image' => __('No se pudo guardar el evento ni sus imágenes. Inténtalo de nuevo y, si sigue fallando, avisa al equipo web. (Detalle: :reason)', ['reason' => $reason])]);
    }

    private function folderFor(Event $event): string
    {
        return "events-workshops/{$event->id}";
    }

    /**
     * @return array<int, string>
     */
    private function storeGallery(EventRequest $request, Event $event): array
    {
        return collect($request->file('gallery', []))
            ->map(fn (UploadedFile $photo): string => ($this->storeWebpImage)($photo, $this->folderFor($event).'/gallery'))
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $eventIds
     * @return Collection<int, int>
     */
    private function registrationCounts(Collection $eventIds): Collection
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
}
