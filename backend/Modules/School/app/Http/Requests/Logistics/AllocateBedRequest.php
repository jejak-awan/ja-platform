<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class AllocateBedRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'bed_id' => 'required|exists:sch_log_hostel_beds,id',
            'start_date' => 'required|date',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
