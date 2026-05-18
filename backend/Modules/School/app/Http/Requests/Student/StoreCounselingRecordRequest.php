<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCounselingRecordRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'student_id' => 'required|exists:sch_std_students,id',
            'staff_id' => 'required|exists:sch_hr_staff,id', // counselor
            'date' => 'required|date',
            'type' => 'required|string|max:100',
            'problem' => 'required|string',
            'solution' => 'nullable|string',
            'status' => 'required|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
