<?php

namespace App\Http\Requests;

use App\Models\Investment;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvestmentTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Investment|null $investment */
        $investment = $this->route('investment');

        return $this->user() !== null
            && $investment !== null
            && (int) $investment->user_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'type' => ['required', Rule::in(['buy', 'sell'])],
            'transaction_date' => ['required', 'date'],
            'units' => ['required', 'numeric', 'min:0.00000001'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Investment|null $investment */
            $investment = $this->route('investment');

            if (! $investment) {
                return;
            }

            if ($this->input('type') === 'sell' && (float) $this->input('units') > (float) $investment->units) {
                $validator->errors()->add('units', 'Unit yang dijual melebihi kepemilikan saat ini.');
            }
        });
    }
}
