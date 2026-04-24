<?php

namespace Modules\School\Http\Requests\Lms;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionBankRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'subject_id' => 'required|exists:sch_acad_subjects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
