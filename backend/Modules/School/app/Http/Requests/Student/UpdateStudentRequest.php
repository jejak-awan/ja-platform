<?php

namespace Modules\School\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \Modules\School\Models\Student\Student|null $student */
        $student = $this->route('student');
        $id = $student instanceof \Modules\School\Models\Student\Student ? $student->id : $this->input('id');
        $studentId = is_numeric($id) ? (int)$id : 0;

        return [
            'level_id' => 'nullable|exists:sch_ins_levels,id',
            'department_id' => 'nullable|exists:sch_acad_departments,id',

            // Biography
            'full_name' => 'required|string|max:255',
            'nisn' => 'nullable|string|size:10|unique:sch_std_students,nisn,' . $studentId,
            'nis' => 'nullable|string|max:20',
            'gender' => 'required|in:L,P',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'nik' => 'nullable|string|size:16',
            'religion' => 'nullable|string|max:20',
            'special_needs' => 'nullable|string',

            // Address
            'address' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'dusun' => 'nullable|string|max:100',
            'desa_kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'residence_type' => 'nullable|string|max:50',
            'transportation' => 'nullable|string|max:50',

            // Contact
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',

            // Parents
            'father_name' => 'nullable|string|max:255',
            'father_nik' => 'nullable|string|size:16',
            'father_birth_year' => 'nullable|integer',
            'father_education' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string|max:50',
            'father_income' => 'nullable|string|max:50',

            'mother_name' => 'nullable|string|max:255',
            'mother_nik' => 'nullable|string|size:16',
            'mother_birth_year' => 'nullable|integer',
            'mother_education' => 'nullable|string|max:50',
            'mother_occupation' => 'nullable|string|max:50',
            'mother_income' => 'nullable|string|max:50',

            'guardian_name' => 'nullable|string|max:255',
            'guardian_nik' => 'nullable|string|size:16',
            'guardian_birth_year' => 'nullable|integer',
            'guardian_education' => 'nullable|string|max:50',
            'guardian_occupation' => 'nullable|string|max:50',
            'guardian_income' => 'nullable|string|max:50',

            'weight' => 'nullable|integer',
            'height' => 'nullable|integer',
            'mileage' => 'nullable|integer',
            'travel_time_minutes' => 'nullable|integer',
            'siblings_count' => 'nullable|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
