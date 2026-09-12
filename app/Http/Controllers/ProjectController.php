<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Contracts\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::orderByDesc('start_date')->get();

        $categories = $projects->pluck('category')->unique()->values();

        return view('projects.index', compact('projects', 'categories'));
    }
}
