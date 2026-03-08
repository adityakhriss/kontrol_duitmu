<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\PaymentAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'payment_account_id' => [
                'required',
                Rule::exists(PaymentAccount::class, 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'category_id' => [
                'nullable',
                Rule::exists(Category::class, 'id')->where(fn ($query) => $query->where(fn ($inner) => $inner
                    ->whereNull('user_id')
                    ->orWhere('user_id', $this->user()->id))),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('category_id')) {
                return;
            }

            $category = Category::query()->find($this->integer('category_id'));

            if ($category && $category->type !== $this->input('type')) {
                $validator->errors()->add('category_id', 'Kategori tidak sesuai dengan jenis transaksi.');
            }
        });
    }
}
