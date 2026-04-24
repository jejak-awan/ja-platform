<?php

namespace Modules\School\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'subject_id' => 'required|exists:sch_acad_subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'passing_grade' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
