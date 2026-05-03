<?php

namespace Modules\School\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreCounselingRecordRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_unit_id' => 'required|exists:sch_ins_levels,id',
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
