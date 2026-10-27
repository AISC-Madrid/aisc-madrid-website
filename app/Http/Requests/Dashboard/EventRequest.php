<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * Create / edit an event from the dashboard (like the legacy create_event.php form).
 * Dates come from datetime-local inputs in Madrid time and are stored in UTC.
 */
class EventRequest extends FormRequest
{
    public const TIMEZONE = 'Europe/Madrid';

    public function authorize(): bool
    {
        $member = $this->user();

        return $member instanceof Member && $member->hasAnyRole(['admin', 'events']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isCreating = $this->route('event') === null;

        return [
            'title_es' => ['required', 'string', 'max:255'],
            'title_en' => ['required', 'string', 'max:255'],
            'type_es' => ['nullable', 'string', 'max:100'],
            'type_en' => ['nullable', 'string', 'max:100'],
            'speaker' => ['nullable', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:150'],
            'description_es' => ['required', 'string'],
            'description_en' => ['required', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after_or_equal:start_datetime'],
            'image' => [$isCreating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
            'gallery' => ['nullable', 'array', 'max:30'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
            'youtube_url' => ['nullable', 'url', 'max:355'],
            'requires_registration' => ['boolean'],
            'reminder_enabled' => ['boolean'],
            'reminder_days_before' => ['required', 'integer', 'between:1,7'],
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
            'description_es' => 'descripción (español)',
            'description_en' => 'descripción (inglés)',
            'start_datetime' => 'inicio',
            'end_datetime' => 'fin',
            'image' => 'imagen principal',
            'gallery.*' => 'foto del evento',
            'youtube_url' => 'URL de YouTube',
            'reminder_days_before' => 'días de antelación',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_registration' => $this->boolean('requires_registration'),
            'reminder_enabled' => $this->boolean('reminder_enabled'),
        ]);
    }

    /**
     * Validated event columns, with empty speaker defaulting like the table and dates in UTC.
     *
     * @return array<string, mixed>
     */
    public function eventAttributes(): array
    {
        $data = collect($this->validated())->except(['image', 'gallery'])->all();

        $data['speaker'] = filled($data['speaker'] ?? null) ? $data['speaker'] : 'AISC Madrid';
        $data['start_datetime'] = Carbon::parse($data['start_datetime'], self::TIMEZONE)->utc();
        $data['end_datetime'] = Carbon::parse($data['end_datetime'], self::TIMEZONE)->utc();

        return $data;
    }
}
