<?php

namespace Modules\School\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;

class PayBillRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \Modules\School\Models\Finance\StudentBill|null $bill */
        $bill = $this->route('bill');
        $maxAmount = $bill ? ((float)$bill->amount - (float)$bill->paid_amount) : 0.0;

        return [
            'amount' => 'required|numeric|min:1|max:' . $maxAmount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
