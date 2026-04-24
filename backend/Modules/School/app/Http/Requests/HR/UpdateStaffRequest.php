<?php

namespace Modules\School\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \Modules\School\Models\HR\Staff|null $staff */
        $staff = $this->route('staff');
        $id = $staff instanceof \Modules\School\Models\HR\Staff ? $staff->id : $this->input('id');
        $staffId = is_numeric($id) ? (int)$id : 0;

        return [
            'full_name' => 'required|string|max:255',
            'nik' => 'nullable|string|size:16',
            'nuptk' => 'nullable|string|size:16|unique:sch_hr_staff,nuptk,' . $staffId,
            'nip' => 'nullable|string|max:20',
            'ptk_type' => 'nullable|string',
            'gender' => 'required|in:L,P',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'religion' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'employment_status' => 'nullable|string|max:50',
            'sk_pengangkatan' => 'nullable|string|max:100',
            'tmt_pengangkatan' => 'nullable|date',
            'sk_penugasan' => 'nullable|string|max:100',
            'tmt_penugasan' => 'nullable|date',
            'last_education' => 'nullable|string|max:50',
            'major' => 'nullable|string|max:100',
            'certification_status' => 'nullable|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
