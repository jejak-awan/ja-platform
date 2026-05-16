<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreUksVisitRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'patient_type' => 'required|string',
            'patient_id' => 'required|integer',
            'complaint' => 'required|string',
            'treatment' => 'nullable|string',
            'medicine_given' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
