<?php

namespace App\Http\Requests;

use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'transfer_date' => ['required', 'date'],
            'from_payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'to_payment_account_id' => [
                'required',
                'different:from_payment_account_id',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
