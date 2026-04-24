<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'plate_number' => 'required|string|unique:sch_log_vehicles,plate_number',
            'model' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'driver_name' => 'nullable|string',
            'driver_phone' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
