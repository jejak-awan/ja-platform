<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Academic;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:H,I,S,A',
            'notes' => 'nullable|string',
            'attachment_path' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
