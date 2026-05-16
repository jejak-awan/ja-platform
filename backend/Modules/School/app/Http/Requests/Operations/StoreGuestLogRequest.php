<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestLogRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'name' => 'required|string|max:255',
            'institution' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'visit_time' => 'required|date',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
