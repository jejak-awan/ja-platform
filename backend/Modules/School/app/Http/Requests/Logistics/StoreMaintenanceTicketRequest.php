<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaintenanceTicketRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
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
