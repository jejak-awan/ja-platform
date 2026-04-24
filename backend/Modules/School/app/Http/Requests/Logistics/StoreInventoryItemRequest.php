<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryItemRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'category_id' => 'required|exists:sch_log_inventory_categories,id',
            'sku' => 'required|string|unique:sch_log_inventory_items,sku',
            'name' => 'required|string|max:255',
            'unit' => 'required|string',
            'quantity_on_hand' => 'required|integer|min:0',
            'minimum_stock' => 'nullable|integer|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
