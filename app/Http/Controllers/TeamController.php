<?php

namespace App\Http\Controllers;

use App\Models\Member;

class TeamController extends Controller
{
    public function index()
    {
        return view('team.index', [
            'activeMembers' => Member::activeMembers()->orderBy('id')->get(),
            'honorMembers'  => Member::honorMembers()->with('alumniHonor')->orderBy('id')->get(),
        ]);
    }
}