<?php

namespace App\Http\Requests\Admin;

use App\Enums\Permission;
use App\Models\Admin;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('roles', 'name')->where('guard_name', Permission::GUARD)->ignore($this->route('role')?->id),
                function (string $attribute, mixed $value, Closure $fail) {
                    if (strcasecmp(trim((string) $value), Admin::SUPER_ADMIN) === 0) {
                        $fail(__('That role name is reserved.'));
                    }
                },
            ],
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(Permission::class)],
        ];
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return array_values($this->validated('permissions', []));
    }
}
