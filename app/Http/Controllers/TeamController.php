<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Contracts\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('team.index', [
            'activeMembers' => Member::activeMembers()->orderBy('id')->get(),
            'honorMembers' => Member::honorMembers()->with('alumniHonor')->orderBy('id')->get(),
        ]);
    }
}
