<?php

namespace App\Http\Requests;

use App\Models\PaymentAccount;
use App\Models\SavingGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSavingGoalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SavingGoal|null $goal */
        $goal = $this->route('savingGoal');

        return $this->user() !== null
            && $goal !== null
            && (int) $goal->user_id === (int) $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'entry_type' => ['required', Rule::in(['deposit', 'withdraw'])],
            'entry_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
