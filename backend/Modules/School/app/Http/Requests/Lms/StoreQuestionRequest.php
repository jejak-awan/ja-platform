<?php

namespace Modules\School\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'type' => 'required|string|in:multiple_choice,essay,true_false',
            'level' => 'required|string|in:easy,medium,hard',
            'options' => 'nullable|array',
            'answer' => 'required|string',
            'media_path' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
