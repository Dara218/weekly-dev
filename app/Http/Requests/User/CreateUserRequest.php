<?php

namespace App\Http\Requests\User;

use App\Enum\{
    Gender,
    Subjects,
    TeacherClassAssignmentStatus,
    UserRole,
};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CreateUserRequest extends FormRequest
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
                Rule::unique('users', 'email')->whereNull('deleted_at'),
                'max:' . config('constant.validation.max.email'),
            ],
            'password' => [
                'required',
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

    /**
     * Get user-type specific validation rules.
     *
     * @param string $userRole
     *
     * @return array<mixed>
     */
    public function getUserTypeSpecificRules(string $userRole): array
    {
        $phoneRules = [
            'nullable',
            'string',
            'regex:/^(09\d{9}|\+639\d{9})$/',
        ];
        $addressRules = [
            'required',
            'max:' . config('constant.validation.max.address'),
        ];

        return match ($userRole) {
            UserRole::STUDENT->value => [
                'gender' => [
                    'required',
                    Rule::in(Gender::list()),
                ],
                'dob' => [
                    'required',
                    'date',
                ],
                'class_id' => [
                    'required',
                    Rule::exists('teacher_class_assignments', 'class_id')->where(function ($query) {
                        $query->where('teacher_id', $this->teacher_id);
                    }),
                ],
                'section_id' => [
                    'required',
                    Rule::exists('teacher_class_assignments', 'section_id')->where(function ($query) {
                        $query->where('teacher_id', $this->teacher_id)
                            ->where('status', TeacherClassAssignmentStatus::ACTIVE->value);
                    }),
                ],
                'admission_no' => [
                    'nullable',
                ],
                'parent_id' => [
                    'required',
                    Rule::exists('parents', 'id')->where(function ($query) {
                        $query->where('id', $this->parent_id);
                    }),
                ],
                'phone' => $phoneRules,
                'address' => $addressRules,
            ],
            UserRole::PARENT->value => [
                //
            ],
            UserRole::TEACHER->value => [
                'employee_code' => [
                    'required',
                ],
                'phone' => $phoneRules,
                'address' => $addressRules,
                'specialization' => [
                    'required',
                    Rule::in(Subjects::list()),
                ],
                'experience_years' => [
                    'required',
                    'integer'
                ],
                'classes' => [
                    'nullable'
                ],
            ],
            default => [],
        };
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'phone.regex' => __('validation.regex.phone')
        ];
    }
}
