<?php

namespace App\Http\Requests\FileUpload;

use Illuminate\Foundation\Http\FormRequest;

class StudentFileUploadRequest extends FormRequest
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
        return [
            'id' => [
                'required',
            ],
            'file' => [
                'required',
                'file',
                'mimes:' . config('constant.validation.student_file_mimes'),
                'max:' . config('constant.validation.max.file_size'),
            ],
        ];
    }
}
