<?php

namespace App\Http\Controllers;

use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $upcoming = Event::with('type')
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime', 'asc')
            ->get();

        $past = Event::with('type')
            ->where('end_datetime', '<', now())
            ->orderBy('start_datetime', 'desc')
            ->get();

        $events = $upcoming->concat($past);

        return view('events.index', compact('events'));
    }

    public function show(string $locale, Event $event)
    {
        $event->load([
            'type',
            'speakers',
        ]);

        return view('events.show', compact('event'));
    }
}
