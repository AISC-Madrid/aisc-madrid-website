<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Create / edit a project from the dashboard (admin / web roles).
 */
class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user();

        return $member instanceof Member && $member->hasAnyRole(['admin', 'web']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreating = $this->route('project') === null;

        return [
            'title_es' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description_es' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'tags' => ['nullable', 'string', 'max:255'],
            'team_credit' => ['nullable', 'string', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'external_url' => ['nullable', 'url', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'image' => [$isCreating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title_es' => 'título (español)',
            'title_en' => 'título (inglés)',
            'category' => 'categoría',
            'description_es' => 'descripción (español)',
            'description_en' => 'descripción (inglés)',
            'team_credit' => 'créditos del equipo',
            'github_url' => 'URL de GitHub',
            'external_url' => 'URL externa',
            'start_date' => 'fecha de inicio',
            'end_date' => 'fecha de fin',
            'image' => 'imagen principal',
            'gallery.*' => 'foto del proyecto',
        ];
    }

    /**
     * Validated project columns, with the comma-separated tags turned into an array.
     *
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        $data = collect($this->validated())->except(['image', 'gallery'])->all();

        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->values()
            ->all();

        return $data;
    }
}
