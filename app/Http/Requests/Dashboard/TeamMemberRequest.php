<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\MemberRole;
use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Create / edit a team member from the dashboard (like the legacy create_team_member.php form).
 * The password is optional when editing: empty keeps the current one.
 */
class TeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user();

        return $member instanceof Member && $member->hasAnyRole(['admin', 'finance']);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Member|null $teamMember */
        $teamMember = $this->route('member');

        return [
            'full_name' => ['required', 'string', 'max:50'],
            'mail' => ['required', 'email', 'max:50', Rule::unique('members', 'mail')->ignore($teamMember?->id)],
            'password' => [$teamMember === null ? 'required' : 'nullable', 'string', 'min:6', 'max:255'],
            'position_es' => ['nullable', 'string', 'max:50'],
            'position_en' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'dni' => ['nullable', 'string', 'max:9'],
            'socials' => ['nullable', 'string', 'max:2000'],
            'role' => ['required', Rule::enum(MemberRole::class)],
            'board' => ['boolean'],
            'active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'],
            'is_honor' => ['boolean'],
            'graduation_year' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'honor_quote' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'nombre y apellidos',
            'mail' => 'email',
            'password' => 'contraseña',
            'position_es' => 'posición (español)',
            'position_en' => 'posición (inglés)',
            'phone' => 'teléfono',
            'socials' => 'red social',
            'role' => 'rol',
            'image' => 'foto',
            'graduation_year' => 'año de graduación',
            'honor_quote' => 'frase personal',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'board' => $this->boolean('board'),
            'active' => $this->boolean('active'),
            'is_honor' => $this->boolean('is_honor'),
        ]);
    }

    /**
     * Validated members columns, with the password hashed only when one was typed.
     *
     * @return array<string, mixed>
     */
    public function memberAttributes(): array
    {
        $data = collect($this->validated())
            ->only(['full_name', 'mail', 'position_es', 'position_en', 'phone', 'dni', 'socials', 'role', 'board', 'active'])
            ->all();

        if (filled($this->validated('password'))) {
            $data['password_hash'] = Hash::make($this->validated('password'));
        }

        return $data;
    }

    /**
     * Alumni honor row to keep, or null when the member is not an honor member.
     *
     * @return array{graduation_year: int|null, honor_quote: string|null}|null
     */
    public function honorAttributes(): ?array
    {
        if (! $this->validated('is_honor')) {
            return null;
        }

        $year = $this->validated('graduation_year');

        return [
            'graduation_year' => $year === null ? null : (int) $year,
            'honor_quote' => $this->validated('honor_quote'),
        ];
    }
}
