<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Guest;
use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Create / edit a guest from the dashboard (like the legacy create_guest.php and edit_guest.php).
 * The password is optional when editing: empty keeps the current one.
 */
class GuestRequest extends FormRequest
{
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
        /** @var Guest|null $guest */
        $guest = $this->route('guest');

        return [
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('guests', 'username')->ignore($guest?->id)],
            'password' => [$guest === null ? 'required' : 'nullable', 'string', 'min:6', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
            'events' => ['array'],
            'events.*' => ['integer', 'exists:events,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'usuario',
            'password' => 'contraseña',
            'name' => 'nombre',
            'events.*' => 'evento',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Validated guest columns, with the password hashed only when one was typed.
     *
     * @return array<string, mixed>
     */
    public function guestAttributes(): array
    {
        $data = collect($this->validated())->only(['username', 'name', 'email', 'is_active'])->all();

        if (filled($this->validated('password'))) {
            $data['password_hash'] = Hash::make($this->validated('password'));
        }

        return $data;
    }

    /**
     * @return array<int, int>
     */
    public function eventIds(): array
    {
        return array_map('intval', $this->validated('events', []));
    }
}
