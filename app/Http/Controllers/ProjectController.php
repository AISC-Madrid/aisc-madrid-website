<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $categories = $projects->pluck('category')->unique()->values();

        return view('projects.index', compact('projects', 'categories'));
    }

    public function show(string $locale, Project $project): View
    {
        return view('projects.show', compact('project'));
    }
}
