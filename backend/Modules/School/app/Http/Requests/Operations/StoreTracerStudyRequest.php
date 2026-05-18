<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTracerStudyRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'alumni_id' => 'required|exists:sch_ops_alumni,id',
            'employment_status' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'university_name' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'salary_range' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'is_relevant_to_major' => 'required|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
