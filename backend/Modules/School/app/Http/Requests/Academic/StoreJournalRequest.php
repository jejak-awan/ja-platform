<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Academic;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJournalRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'workspace_id' => 'required|exists:sch_ins_levels,id',
            'schedule_id' => 'required|exists:sch_acad_schedules,id',
            'date' => 'required|date',
            'topic' => 'required|string|max:255',
            'materials' => 'nullable|string',
            'absent_students' => 'nullable|array',
            'notes' => 'nullable|string',
            'evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
