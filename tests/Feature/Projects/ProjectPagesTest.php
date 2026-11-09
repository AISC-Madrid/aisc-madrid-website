<?php

use App\Models\Project;

test('projects index lists projects newest first', function () {
    $oldest = Project::factory()->create([
        'title_en' => 'Old project',
        'start_date' => '2024-01-01',
    ]);
    $newest = Project::factory()->create([
        'title_en' => 'New project',
        'start_date' => '2025-01-01',
    ]);

    $this->get(route('projects.index', ['locale' => 'en']))
        ->assertSuccessful()
        ->assertSeeInOrder(['New project', 'Old project']);
});

test('project detail page renders localized content and links', function () {
    $project = Project::factory()->create([
        'title_es' => 'Proyecto español',
        'title_en' => 'English project',
        'description_es' => 'Descripción del proyecto',
        'description_en' => 'Project description',
        'github_url' => 'https://github.com/example/project',
        'external_url' => 'https://example.com/project',
    ]);

    $this->get(route('projects.show', ['locale' => 'es', 'project' => $project]))
        ->assertSuccessful()
        ->assertSee('Proyecto español')
        ->assertSee('Descripción del proyecto')
        ->assertSee('https://github.com/example/project', false);

    $this->get(route('projects.show', ['locale' => 'en', 'project' => $project]))
        ->assertSuccessful()
        ->assertSee('English project')
        ->assertSee('Project description');
});
