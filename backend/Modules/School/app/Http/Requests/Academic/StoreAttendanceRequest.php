<?php

namespace Modules\School\Http\Requests\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'semester_id' => 'required|exists:sch_acad_semesters,id',
            'date' => 'required|date',
            'status' => 'required|in:H,I,S,A', // Hadir, Izin, Sakit, Alpa
            'notes' => 'nullable|string',
            'attachment_path' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
