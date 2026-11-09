<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Create / edit a contact from the dashboard (admin / marketing roles).
 */
class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user();

        return $member instanceof Member && $member->hasAnyRole(['admin', 'marketing']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'greeting_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($this->route('contact'))],
            'category' => ['required', 'string', 'max:100'],
            'organization' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nombre completo',
            'greeting_name' => 'nombre para el saludo',
            'category' => 'categoría',
            'organization' => 'organización',
            'notes' => 'notas',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['active' => $this->boolean('active')]);
    }
}
