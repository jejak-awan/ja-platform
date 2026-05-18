<?php

declare(strict_types=1);

namespace Modules\School\Http\Requests\Institution;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'is_multi_unit' => 'sometimes|boolean',
            'is_multi_branch' => 'sometimes|boolean',
            'initial_level' => 'nullable|string|in:sd,smp,sma,smk',
            'initial_level_name' => 'nullable|string|max:255',
            'npsn' => 'nullable|string|size:8|unique:sch_ins_schools,npsn',
            'nss' => 'nullable|string|max:20',
            'nds' => 'nullable|string|max:20',
            'status_kepemilikan' => 'nullable|string|max:100',
            'accreditation' => 'nullable|string|max:5',
            'kurikulum' => 'nullable|string|max:100',
            'sk_pendirian' => 'nullable|string|max:255',
            'tgl_sk_pendirian' => 'nullable|date',
            'sk_operasional' => 'nullable|string|max:255',
            'tgl_sk_operasional' => 'nullable|date',
            'address' => 'nullable|string',
            'rt' => 'nullable|string|max:3',
            'rw' => 'nullable|string|max:3',
            'dusun' => 'nullable|string|max:100',
            'desa_kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:5',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'npwp' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
            'principal_name' => 'nullable|string|max:255',
            'foundation_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
