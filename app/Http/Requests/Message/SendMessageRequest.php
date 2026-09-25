<?php

namespace App\Http\Requests\Message;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'receiver_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'body' => [
                'required',
                'string',
                'max:' . config('constant.validation.max.message_body'),
            ],
            'attachment' => [
                'nullable',
                'string',
                'max:' . config('constant.validation.max.message_attachment'),
            ],
        ];
    }
}
