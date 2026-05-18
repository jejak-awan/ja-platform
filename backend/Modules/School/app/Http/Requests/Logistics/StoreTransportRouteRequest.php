<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTransportRouteRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'name' => 'required|string|max:255',
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'stops' => 'nullable|array',
            'fee' => 'required|numeric|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
