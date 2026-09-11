<?php

namespace App\Http\Controllers;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderByDesc('start_date')->get();

        $categories = $projects->pluck('category')->unique()->values();

        return view('projects.index', compact('projects', 'categories'));
    }
}
