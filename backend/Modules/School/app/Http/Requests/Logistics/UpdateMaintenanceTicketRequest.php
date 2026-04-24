<?php

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaintenanceTicketRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:Open,In Progress,Resolved',
            'priority' => 'nullable|in:Low,Medium,High',
            'resolution_notes' => 'nullable|string',
            'date_resolved' => 'nullable|date',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
