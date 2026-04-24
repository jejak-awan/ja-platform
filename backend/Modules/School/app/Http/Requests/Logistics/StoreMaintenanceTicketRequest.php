<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceTicketRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_school_levels,id',
            'school_asset_id' => 'required|exists:sch_log_assets,id',
            'reported_by' => 'required|exists:sch_hr_staff,id',
            'date_reported' => 'required|date',
            'issue_description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
