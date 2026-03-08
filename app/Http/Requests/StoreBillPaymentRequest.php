<?php

namespace App\Http\Requests;

use App\Models\Bill;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Bill|null $bill */
        $bill = $this->route('bill');

        return $this->user() !== null
            && $bill !== null
            && (int) $bill->user_id === (int) $this->user()->id;
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
            /** @var Bill|null $bill */
            $bill = $this->route('bill');

            if (! $bill) {
                return;
            }

            if ($bill->status === 'paid' && ! $bill->is_recurring) {
                $validator->errors()->add('amount', 'Tagihan ini sudah dibayar.');

                return;
            }

            if ((float) $this->input('amount') !== (float) $bill->amount) {
                $validator->errors()->add('amount', 'Nominal pembayaran harus sama dengan nominal tagihan.');
            }
        });
    }
}
