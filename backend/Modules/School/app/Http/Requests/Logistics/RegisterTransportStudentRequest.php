<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Logistics;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterTransportStudentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:sch_std_students,id',
            'vehicle_id' => 'required|exists:sch_log_vehicles,id',
            'route_id' => 'required|exists:sch_log_vehicle_routes,id',
            'pickup_point' => 'nullable|string',
            'start_date' => 'required|date',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
