<?php

namespace Modules\School\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeTypeRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_levels,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0',
            'period' => 'required|in:monthly,quarterly,yearly,once',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
