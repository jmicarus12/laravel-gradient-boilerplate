<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Create and edit an admin. On create a password is required; on edit it's a separate form.
 */
class AdminRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(Admin::class)->ignore($admin?->id)->withoutTrashed(),
            ],
            'password' => $admin ? ['prohibited'] : ['required', 'confirmed', Password::defaults()],
            'active' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ];
    }

    /**
     * @return array{name: string, email: string, password?: string, active: bool}
     */
    public function account(): array
    {
        return [...$this->safe()->only(['name', 'email', 'password']), 'active' => $this->boolean('active')];
    }

    /**
     * @return list<string>
     */
    public function roles(): array
    {
        return array_values($this->validated('roles', []));
    }
}
