<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Contracts\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $upcoming = Event::query()
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime', 'asc')
            ->get();

        $past = Event::query()
            ->where('end_datetime', '<', now())
            ->orderBy('start_datetime', 'desc')
            ->get();

        $events = $upcoming->concat($past);

        return view('events.index', compact('events'));
    }

    public function show(string $locale, Event $event): View
    {
        $event->load('speakers');

        return view('events.show', compact('event'));
    }
}
