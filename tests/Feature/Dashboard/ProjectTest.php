<?php

use App\Actions\StoreWebpImage;
use App\Enums\MemberRole;
use App\Models\Member;
use App\Models\Project;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToWriteFile;

/**
 * @return array<string, mixed>
 */
function validProjectInput(array $overrides = []): array
{
    return [
        'title_es' => 'Proyecto de IA',
        'title_en' => 'AI Project',
        'category' => 'Software',
        'description_es' => 'Descripción en español.',
        'description_en' => 'English description.',
        'tags' => 'laravel, ia , web',
        'team_credit' => 'Equipo web',
        'github_url' => 'https://github.com/AISC-Madrid/proyecto',
        'external_url' => '',
        'start_date' => '2026-01-10',
        'end_date' => '2026-06-30',
        ...$overrides,
    ];
}

function projectsManager(): Member
{
    return Member::factory()->role(MemberRole::Web)->create();
}

beforeEach(function () {
    Storage::fake('s3');
});

test('guests are redirected to login', function () {
    $this->get(route('dashboard.projects.index'))->assertRedirect(route('login'));
});

test('projects are listed with title, status, category and dates', function () {
    Project::factory()->create([
        'title_es' => 'Proyecto terminado',
        'category' => 'Software',
        'start_date' => '2025-01-10',
        'end_date' => '2025-06-30',
    ]);
    Project::factory()->create([
        'title_es' => 'Proyecto activo',
        'category' => 'Hardware',
        'start_date' => '2025-09-01',
        'end_date' => null,
    ]);

    $this->actingAs(projectsManager())
        ->get(route('dashboard.projects.index'))
        ->assertOk()
        ->assertSee('Proyecto terminado')
        ->assertSee('Finalizado')
        ->assertSee('Software')
        ->assertSee('10/01/2025')
        ->assertSee('30/06/2025')
        ->assertSee('Proyecto activo')
        ->assertSee('En curso')
        ->assertSee('actualidad');
});

test('the create form can be rendered', function () {
    $this->actingAs(projectsManager())
        ->get(route('dashboard.projects.create'))
        ->assertOk()
        ->assertSee('Crear nuevo proyecto')
        ->assertSee('name="gallery[]"', false);
});

test('projects can be created with a main image and gallery stored as WebP in the bucket', function () {
    $this->actingAs(projectsManager())
        ->post(route('dashboard.projects.store'), validProjectInput([
            'image' => UploadedFile::fake()->image('cover.jpg', 800, 800),
            'gallery' => [
                UploadedFile::fake()->image('one.png', 400, 300),
                UploadedFile::fake()->image('two.jpg', 400, 300),
            ],
        ]))
        ->assertRedirect(route('dashboard.projects.index'))
        ->assertSessionHas('status');

    $project = Project::sole();

    expect($project)
        ->title_es->toBe('Proyecto de IA')
        ->title_en->toBe('AI Project')
        ->category->toBe('Software')
        ->tags->toBe(['laravel', 'ia', 'web']);

    expect($project->image_path)->toStartWith("projects/{$project->id}/img_")->toEndWith('.webp');
    Storage::disk('s3')->assertExists($project->image_path);

    expect($project->gallery_paths)->toHaveCount(2);
    foreach ($project->gallery_paths as $path) {
        expect($path)->toStartWith("projects/{$project->id}/gallery/img_");
        Storage::disk('s3')->assertExists($path);
    }
});

test('creating a project validates the input', function () {
    $this->actingAs(projectsManager())
        ->post(route('dashboard.projects.store'), validProjectInput([
            'title_es' => '',
            'end_date' => '2025-01-01',
            'github_url' => 'not a url',
            'gallery' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ]))
        ->assertSessionHasErrors(['title_es', 'end_date', 'image', 'github_url', 'gallery.0']);

    expect(Project::count())->toBe(0);
});

test('the edit form shows the current values', function () {
    $project = Project::factory()->create([
        'title_es' => 'Proyecto de IA',
        'start_date' => '2026-01-10',
        'image_path' => 'projects/5/cover.webp',
        'gallery_paths' => ['projects/5/gallery/01.webp'],
    ]);

    $this->actingAs(projectsManager())
        ->get(route('dashboard.projects.edit', $project))
        ->assertOk()
        ->assertSee('Editar proyecto')
        ->assertSee('value="Proyecto de IA"', false)
        ->assertSee('value="2026-01-10"', false)
        ->assertSee('projects/5/cover.webp', false)
        ->assertSee('projects/5/gallery/01.webp', false);
});

test('updating without new images keeps the current ones', function () {
    $project = Project::factory()->create([
        'image_path' => 'projects/5/cover.webp',
        'gallery_paths' => ['projects/5/gallery/01.webp'],
    ]);

    $this->actingAs(projectsManager())
        ->put(route('dashboard.projects.update', $project), validProjectInput(['title_es' => 'Título nuevo']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard.projects.index'));

    expect($project->refresh())
        ->title_es->toBe('Título nuevo')
        ->image_path->toBe('projects/5/cover.webp')
        ->gallery_paths->toBe(['projects/5/gallery/01.webp']);
});

test('new gallery photos replace the whole gallery and a new image replaces the cover', function () {
    $project = Project::factory()->create([
        'image_path' => 'projects/5/cover.webp',
        'gallery_paths' => ['projects/5/gallery/01.webp', 'projects/5/gallery/02.webp'],
    ]);

    $this->actingAs(projectsManager())
        ->put(route('dashboard.projects.update', $project), validProjectInput([
            'image' => UploadedFile::fake()->image('new-cover.jpg'),
            'gallery' => [UploadedFile::fake()->image('new.jpg')],
        ]))
        ->assertSessionHasNoErrors();

    $project->refresh();

    expect($project->image_path)->not->toBe('projects/5/cover.webp')->toStartWith("projects/{$project->id}/img_");
    expect($project->gallery_paths)->toHaveCount(1);
    Storage::disk('s3')->assertExists($project->gallery_paths[0]);
});

test('projects can be deleted', function () {
    $project = Project::factory()->create(['title_es' => 'Proyecto a borrar']);

    $this->actingAs(projectsManager())
        ->delete(route('dashboard.projects.destroy', $project))
        ->assertRedirect(route('dashboard.projects.index'))
        ->assertSessionHas('status', 'Proyecto «Proyecto a borrar» eliminado.');

    expect(Project::find($project->id))->toBeNull();
});

test('a failed upload sends the member back to the form instead of a 500', function () {
    $this->mock(StoreWebpImage::class, function ($mock) {
        $mock->shouldReceive('__invoke')->andThrow(UnableToWriteFile::atLocation('projects/1/img.webp', 'no credentials'));
    });

    $this->actingAs(projectsManager())
        ->from(route('dashboard.projects.create'))
        ->post(route('dashboard.projects.store'), validProjectInput([
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ]))
        ->assertRedirect(route('dashboard.projects.create'))
        ->assertSessionHasErrors('image')
        ->assertSessionHasInput('title_es', 'Proyecto de IA');

    expect(Project::count())->toBe(0);
});

test('admins can also manage projects', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Admin)->create())
        ->get(route('dashboard.projects.index'))
        ->assertOk();
});

test('members without permission get a 403', function (string $method, string $route) {
    $project = Project::factory()->create();
    $member = Member::factory()->role(MemberRole::Events)->create();
    $global = in_array($route, ['dashboard.projects.index', 'dashboard.projects.create', 'dashboard.projects.store']);

    $this->actingAs($member)
        ->call($method, route($route, $global ? [] : $project))
        ->assertForbidden();

    expect(Project::find($project->id))->not->toBeNull();
})->with([
    ['GET', 'dashboard.projects.index'],
    ['GET', 'dashboard.projects.create'],
    ['POST', 'dashboard.projects.store'],
    ['GET', 'dashboard.projects.edit'],
    ['PUT', 'dashboard.projects.update'],
    ['DELETE', 'dashboard.projects.destroy'],
]);

test('the sidebar shows each link only to members with permission', function () {
    $this->actingAs(Member::factory()->role(MemberRole::Admin)->create())
        ->get(route('dashboard'))
        ->assertSee(route('dashboard.contacts.index'), false)
        ->assertSee(route('dashboard.projects.index'), false);

    $this->actingAs(Member::factory()->role(MemberRole::Events)->create())
        ->get(route('dashboard'))
        ->assertDontSee(route('dashboard.contacts.index'), false)
        ->assertDontSee(route('dashboard.projects.index'), false);
});
