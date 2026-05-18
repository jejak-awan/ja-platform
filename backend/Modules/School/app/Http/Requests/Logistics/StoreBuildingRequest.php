<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBuildingRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'land_asset_id' => 'required|exists:sch_log_land_assets,id',
            'name' => 'required|string|max:255',
            'area' => 'nullable|integer',
            'floor_count' => 'nullable|integer',
            'year_built' => 'nullable|integer',
            'condition' => 'nullable|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
