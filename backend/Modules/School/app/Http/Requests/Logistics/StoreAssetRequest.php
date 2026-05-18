<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssetRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'room_id' => 'required|exists:sch_log_rooms,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:0',
            'condition' => 'nullable|string|max:50',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
