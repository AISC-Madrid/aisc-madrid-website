<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\StoreWebpImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\ProjectRequest;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * Dashboard projects tab: list, create, edit and delete projects (ES/EN texts, main image and
 * gallery stored as WebP in the bucket, like the events tab).
 */
class ProjectController extends Controller
{
    public function __construct(private StoreWebpImage $storeWebpImage) {}

    public function index(): View
    {
        $projects = Project::query()
            ->orderByDesc('start_date')
            ->paginate(25);

        return view('dashboard.projects.index', ['projects' => $projects]);
    }

    public function create(): View
    {
        return view('dashboard.projects.form', ['project' => new Project]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        try {
            // If an upload fails the transaction rolls back, so no half-created project is left behind.
            $project = DB::transaction(function () use ($request): Project {
                $project = Project::create($request->projectAttributes());

                $project->update([
                    'image_path' => ($this->storeWebpImage)($request->file('image'), $this->folderFor($project)),
                    'gallery_paths' => $this->storeGallery($request, $project),
                ]);

                return $project;
            });
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        return to_route('dashboard.projects.index')
            ->with('status', __('Proyecto «:title» creado.', ['title' => $project->title_es]));
    }

    public function edit(Project $project): View
    {
        return view('dashboard.projects.form', ['project' => $project]);
    }

    /**
     * New main image replaces the old one; new gallery photos replace the whole gallery.
     */
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $attributes = $request->projectAttributes();

        try {
            if ($request->hasFile('image')) {
                $attributes['image_path'] = ($this->storeWebpImage)($request->file('image'), $this->folderFor($project));
            }

            if ($request->hasFile('gallery')) {
                $attributes['gallery_paths'] = $this->storeGallery($request, $project);
            }
        } catch (Throwable $exception) {
            return $this->uploadFailed($exception);
        }

        $project->update($attributes);

        return to_route('dashboard.projects.index')
            ->with('status', __('Proyecto «:title» actualizado.', ['title' => $project->title_es]));
    }

    /**
     * Deletes the project row; its images stay in the bucket (same as events).
     */
    public function destroy(Project $project): RedirectResponse
    {
        $title = $project->title_es;

        $project->delete();

        return to_route('dashboard.projects.index')
            ->with('status', __('Proyecto «:title» eliminado.', ['title' => $title]));
    }

    private function uploadFailed(Throwable $exception): RedirectResponse
    {
        report($exception);

        $reason = class_basename($exception).': '.Str::limit($exception->getMessage(), 220);

        return back()
            ->withInput()
            ->withErrors(['image' => __('No se pudo guardar el proyecto ni sus imágenes. Inténtalo de nuevo y, si sigue fallando, avisa al equipo web. (Detalle: :reason)', ['reason' => $reason])]);
    }

    private function folderFor(Project $project): string
    {
        return "projects/{$project->id}";
    }

    /**
     * @return array<int, string>
     */
    private function storeGallery(ProjectRequest $request, Project $project): array
    {
        return collect($request->file('gallery', []))
            ->map(fn (UploadedFile $photo): string => ($this->storeWebpImage)($photo, $this->folderFor($project).'/gallery'))
            ->values()
            ->all();
    }
}
