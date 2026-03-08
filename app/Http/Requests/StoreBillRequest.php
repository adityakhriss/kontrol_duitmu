<?php

namespace App\Http\Requests;

use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'payment_account_id' => [
                'nullable',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'is_recurring' => ['nullable', 'boolean'],
            'recurring_period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
