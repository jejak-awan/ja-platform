<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAchievementRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'title' => 'required|string|max:255',
            'level' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'date' => 'required|date',
            'evidence_path' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
