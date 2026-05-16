<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostelBlockRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'gender' => 'required|in:male,female,mixed',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
