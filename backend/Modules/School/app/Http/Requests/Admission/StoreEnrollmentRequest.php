<?php

namespace Modules\School\Http\Requests\Admission;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'nisn' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'previous_school' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
