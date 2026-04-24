<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreHostelRoomRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'features' => 'nullable|array',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
