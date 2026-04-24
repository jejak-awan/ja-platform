<?php

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class CheckInVisitorRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_school_levels,id',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'institution' => 'nullable|string|max:255',
            'purpose' => 'required|string|max:255',
            'target_person' => 'nullable|string|max:255',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'id_card_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'notes' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
