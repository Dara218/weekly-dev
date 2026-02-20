<?php

namespace App\Http\Requests\User;

use App\Enum\UserRole;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends CreateUserRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userRole = $this->role;

        // Base rules
        $rules = [
            'first_name' => [
                'required',
                'max:' . config('constant.validation.max.first_name'), // 50
            ],
            'middle_name' => [
                'nullable',
                'max:' . config('constant.validation.max.middle_name'), // 50
            ],
            'last_name' => [
                'required',
                'max:' . config('constant.validation.max.last_name'), // 50
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($this->route('id'))
                    ->whereNull('deleted_at'),
                'max:' . config('constant.validation.max.email'),
            ],
            'password' => [
                'nullable',
                Password::min(config('constant.validation.min.password'))
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
                    // ->uncompromised(),
            ],
            'role' => [
                'required',
                Rule::in(UserRole::list()),
            ],
            'profile_image' => [
                'nullable',
                // Todo: add image validation
            ],
            'status' => [
                'required',
            ],
        ];

        return array_merge($rules, $this->getUserTypeSpecificRules($userRole));
    }
}
