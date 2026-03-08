<?php

namespace App\Services\Finance;

use App\Models\PaymentAccount;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function __construct(private readonly PaymentAccountService $paymentAccountService)
    {
    }

    public function create(User $user, array $data): Transaction
    {
        return DB::transaction(function () use ($user, $data): Transaction {
            /** @var PaymentAccount $account */
            $account = PaymentAccount::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['payment_account_id']);

            $transaction = Transaction::query()->create([
                'user_id' => $user->id,
                'payment_account_id' => $account->id,
                'category_id' => $data['category_id'] ?? null,
                'type' => $data['type'],
                'transaction_date' => $data['transaction_date'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->paymentAccountService->applyMutation(
                user: $user,
                account: $account,
                mutationType: 'transaction',
                direction: $data['type'] === 'income' ? 'credit' : 'debit',
                amount: (float) $data['amount'],
                mutationDate: Carbon::parse($data['transaction_date'])->setTime(now()->hour, now()->minute),
                source: $transaction,
                description: $data['notes'] ?? ucfirst($data['type']).' transaction',
            );

            return $transaction->load(['paymentAccount', 'category']);
        });
    }
}
