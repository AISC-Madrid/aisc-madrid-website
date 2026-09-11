<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;

class HomeController extends Controller
{
    public function index()
    {
        $upcomingEvents = Event::with('type')
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->take(3)
            ->get();

        $featuredMembers = Member::activeMembers()
            ->where('board', true)
            ->orderBy('id')
            ->take(4)
            ->get();

        return view('home', compact('upcomingEvents', 'featuredMembers'));
    }
}
