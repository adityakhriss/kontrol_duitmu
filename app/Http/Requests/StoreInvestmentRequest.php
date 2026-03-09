<?php

namespace App\Http\Requests;

use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'ticker' => ['nullable', 'string', 'max:50'],
            'market_symbol' => ['nullable', 'string', 'max:50'],
            'market_exchange' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:30'],
            'platform' => ['nullable', 'string', 'max:255'],
            'payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'transaction_date' => ['required', 'date'],
            'units' => ['required', 'numeric', 'min:0.00000001'],
            'buy_price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
