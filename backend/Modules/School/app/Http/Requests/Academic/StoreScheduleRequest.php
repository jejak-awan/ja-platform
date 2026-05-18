<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Academic;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'semester_id' => 'required|exists:sch_acad_semesters,id',
            'study_group_id' => 'required|exists:sch_acad_study_groups,id',
            'subject_id' => 'required|exists:sch_acad_subjects,id',
            'staff_id' => 'required|exists:sch_hr_staff,id',
            'room_id' => 'nullable|exists:sch_log_rooms,id',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required',
            'end_time' => 'required',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
