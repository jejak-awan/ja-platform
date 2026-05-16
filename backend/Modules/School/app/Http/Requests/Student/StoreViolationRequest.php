<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreViolationRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'category' => 'required|string|max:100',
            'points' => 'required|integer|min:0',
            'date' => 'required|date',
            'description' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
