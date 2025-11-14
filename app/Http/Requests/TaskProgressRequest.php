<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TaskProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'comment' => [
                'required',
                'string',
                'min:5',
                'max:2000',
                'regex:/^[^<>]*$/', // Prevent HTML/script injection
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Progress comment is required.',
            'comment.min' => 'Progress comment must be at least 5 characters.',
            'comment.max' => 'Progress comment cannot exceed 2000 characters.',
            'comment.regex' => 'Progress comment contains invalid characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'comment' => strip_tags(trim($this->comment)),
        ]);
    }
}