<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAlumniRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'graduation_year' => 'required|integer',
            'current_activity' => 'nullable|string|max:255',
            'institution_name' => 'nullable|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
