<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => 'required|exists:sch_log_buildings,id',
            'type' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
            'condition' => 'nullable|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
