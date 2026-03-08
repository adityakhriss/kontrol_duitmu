<?php

namespace App\Http\Requests;

use App\Models\Debt;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDebtPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Debt|null $debt */
        $debt = $this->route('debt');

        return $this->user() !== null
            && $debt !== null
            && (int) $debt->user_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'paid_on' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Debt|null $debt */
            $debt = $this->route('debt');

            if (! $debt) {
                return;
            }

            if ((float) $this->input('amount') > (float) $debt->remaining_amount) {
                $validator->errors()->add('amount', 'Nominal pembayaran melebihi sisa hutang.');
            }
        });
    }
}
