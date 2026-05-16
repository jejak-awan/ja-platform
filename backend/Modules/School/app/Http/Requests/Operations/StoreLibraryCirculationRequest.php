<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibraryCirculationRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'library_book_id' => 'required|exists:sch_ops_library_books,id',
            'borrower_type' => 'required|string',
            'borrower_id' => 'required|integer',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
