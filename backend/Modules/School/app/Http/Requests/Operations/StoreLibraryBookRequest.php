<?php

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryBookRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_id' => 'required|exists:sch_ins_schools,id',
            'isbn' => 'nullable|string|max:20',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:150',
            'publisher' => 'nullable|string|max:150',
            'publish_year' => 'nullable|integer',
            'quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:150',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
