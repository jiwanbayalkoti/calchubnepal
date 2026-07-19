<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('id') !== null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'))],
            'password' => [$isUpdate ? 'nullable' : 'required', 'string', 'min:8'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'is_premium' => ['nullable', 'boolean'],
            'premium_expires_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['is_active', 'is_premium'] as $flag) {
            if ($this->has($flag)) {
                $merge[$flag] = filter_var(
                    $this->input($flag),
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ) ?? false;
            }
        }

        if ($this->input('premium_expires_at') === '') {
            $merge['premium_expires_at'] = null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
