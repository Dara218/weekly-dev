<?php

namespace App\Http\Requests\User;

use App\Enum\{
    Gender,
    StudentStatus,
    UserRole,
};
use App\Interfaces\TeacherClassAssignmentInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Handles validation for bulk student creation requests.
 *
 * Normalizes input rows, enforces per-row field rules via wildcard keys,
 * checks for duplicate emails within the payload, and verifies that each
 * teacher/class/section tuple maps to an active assignment.
 */
class BulkCreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize each row before validation runs.
     *
     * Trims strings, converts blanks to null, forces the role to STUDENT,
     * and uppercases the gender value. Non-array rows are left untouched
     * so that the 'rows.*' => 'array' rule can reject them cleanly.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $rows = $this->input('rows');

        if (! is_array($rows)) {
            return;
        }

        $normalized = collect($rows)
            ->map(fn ($row) => is_array($row) ? $this->normalizeRow($row) : $row)
            ->values()
            ->all();

        $this->merge(['rows' => $normalized]);
    }

    /**
     * Normalize a single row's values.
     *
     * @param  array<string, mixed> $row Raw row from the payload.
     *
     * @return array<string, mixed> Cleaned row ready for validation.
     */
    private function normalizeRow(array $row): array
    {
        return collect($row)
            ->map(fn ($value) => is_string($value) ? (trim($value) ?: null) : $value)
            ->pipe(fn ($collection) => $collection->merge([
                'role' => UserRole::STUDENT->value,
                'gender' => strtoupper((string) ($collection->get('gender') ?? '')),
            ]))
            ->all();
    }

    /**
     * Get the validation rules for the request.
     *
     * Uses wildcard keys so Laravel validates every row natively.
     * Relational integrity for class_id and section_id is handled
     * separately in {@see validateTeacherAssignments()}.
     *
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        $max = fn (string $key) => config("constant.validation.max.{$key}");

        return [
            'rows' => [
                'required',
                'array',
                'min:1',
                "max:{$max('rows')}",
            ],
            'rows.*' => [
                'required',
                'array',
            ],
            'rows.*.first_name' => [
                'required',
                'string',
                "max:{$max('first_name')}",
            ],
            'rows.*.middle_name' => [
                'nullable',
                'string',
                "max:{$max('middle_name')}",
            ],
            'rows.*.last_name' => [
                'required',
                'string',
                "max:{$max('last_name')}",
            ],
            'rows.*.email' => [
                'required',
                'email',
                "max:{$max('email')}",
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'rows.*.password' => [
                'required',
                'string',
                Password::min(config('constant.validation.min.password'))
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
            'rows.*.role' => [
                'required',
                Rule::in([UserRole::STUDENT->value]),
            ],
            'rows.*.status' => [
                'required',
                Rule::in(StudentStatus::list()),
            ],
            'rows.*.gender' => [
                'required',
                Rule::in(Gender::list()),
            ],
            'rows.*.dob' => [
                'required',
                'date',
            ],
            'rows.*.teacher_id' => [
                'required',
                'integer',
                Rule::exists('teachers', 'id')
            ],
            'rows.*.parent_id' => [
                'required',
                'integer',
                Rule::exists('parents', 'id'),
            ],
            'rows.*.class_id' => [
                'required',
                'integer',
            ],
            'rows.*.section_id' => [
                'required',
                'integer',
            ],

            'rows.*.phone' => [
                'nullable',
                'string',
                'regex:/^(09\d{9}|\+639\d{9})$/', // Accepts formats: 09XXXXXXXXX or +639XXXXXXXXX
            ],
            'rows.*.address' => [
                'required',
                'string',
                "max:{$max('address')}",
            ],
        ];
    }

    /**
     * Register after-validation hooks.
     *
     * Runs only when basic field validation has already passed,
     * avoiding unnecessary work on malformed payloads.
     *
     * @param \Illuminate\Validation\Validator $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $rowsInput = $this->input('rows', []);

            if (! is_array($rowsInput)) {
                $rowsInput = [];
            }

            /** @var array<int, array<string, mixed>> $rowsInput */
            $rows = collect($rowsInput);

            $this->validateUniqueEmailsInPayload($validator, $rows);
            $this->validateTeacherAssignments($validator, $rows);
        });
    }

    /**
     * Ensure no two rows in the payload share the same email address.
     *
     * Database uniqueness is already handled by the per-row rule;
     * this catches duplicates *within* a single request.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param \Illuminate\Support\Collection<int, array<string, mixed>> $rows
     *
     * @return void
     */
    private function validateUniqueEmailsInPayload($validator, $rows): void
    {
        $rows->pluck('email')
            ->filter()
            ->map(fn ($email) => strtolower($email))
            ->duplicates()
            ->unique()
            ->each(fn ($email) => $validator->errors()->add(
                'rows',
                "Duplicate email in payload: {$email}"
            ));
    }

    /**
     * Verify that every row's teacher/class/section combination
     * corresponds to an active record in teacher_class_assignments.
     *
     * Fetches all relevant assignments in a single query to avoid
     * N+1 overhead on large payloads.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param \Illuminate\Support\Collection<int, array<string, mixed>>  $rows
     *
     * @return void
     */
    private function validateTeacherAssignments($validator, $rows): void
    {
        $requiredTuples = $rows->map(fn ($row, $index) => [
            'index' => $index,
            'teacher_id' => $row['teacher_id'] ?? null,
            'class_id' => $row['class_id'] ?? null,
            'section_id' => $row['section_id'] ?? null,
        ])->filter(fn ($tuple) => $tuple['teacher_id'] && $tuple['class_id'] && $tuple['section_id']);

        if ($requiredTuples->isEmpty()) {
            return;
        }

        $validAssignments = app(TeacherClassAssignmentInterface::class)
            ->getValidAssignments($requiredTuples);

        $requiredTuples->each(function ($tuple) use ($validator, $validAssignments) {
            $key = "{$tuple['teacher_id']}-{$tuple['class_id']}-{$tuple['section_id']}";

            if (! in_array($key, $validAssignments, true)) {
                $i = $tuple['index'];
                $validator->errors()->add(
                    "rows.{$i}.section_id",
                    "No active teacher assignment exists for the given teacher, class, and section combination."
                );
            }
        });
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rows.*.phone.regex' => __('validation.regex.phone'),
        ];
    }
}
