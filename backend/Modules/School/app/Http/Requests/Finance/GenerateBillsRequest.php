<?php

namespace Modules\School\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class GenerateBillsRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_school_levels,id',
            'fee_type_id' => 'required|exists:sch_fin_fee_types,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'study_group_id' => 'required|exists:sch_acad_study_groups,id',
            'due_date' => 'nullable|date',
            'month' => 'nullable|integer|min:1|max:12',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
