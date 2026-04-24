<?php

namespace Modules\School\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamResultRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exam_id' => 'required|exists:sch_lms_exams,id',
            'student_id' => 'required|exists:sch_std_students,id',
            'status' => 'required|string|max:50',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'answers_snapshot' => 'nullable|array',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
