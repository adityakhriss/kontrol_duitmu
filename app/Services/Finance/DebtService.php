<?php

namespace App\Services\Finance;

use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DebtService
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function create(User $user, array $data): Debt
    {
        return Debt::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'lender_name' => $data['lender_name'] ?? null,
            'total_amount' => $data['total_amount'],
            'remaining_amount' => $data['total_amount'],
            'monthly_payment' => $data['monthly_payment'],
            'interest_rate' => $data['interest_rate'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function recordPayment(User $user, Debt $debt, array $data): DebtPayment
    {
        return DB::transaction(function () use ($user, $debt, $data): DebtPayment {
            if ((float) $data['amount'] > (float) $debt->remaining_amount) {
                throw new InvalidArgumentException('Nominal pembayaran melebihi sisa hutang.');
            }

            /** @var PaymentAccount $account */
            $account = PaymentAccount::query()->where('user_id', $user->id)->findOrFail($data['payment_account_id']);
            $paidOn = Carbon::parse($data['paid_on']);

            $transaction = $this->transactionService->create($user, [
                'transaction_date' => $paidOn->toDateString(),
                'type' => 'expense',
                'payment_account_id' => $account->id,
                'category_id' => $this->resolveExpenseCategoryId($user),
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? 'Bayar hutang '.$debt->name,
            ]);

            $payment = DebtPayment::query()->create([
                'debt_id' => $debt->id,
                'user_id' => $user->id,
                'payment_account_id' => $account->id,
                'transaction_id' => $transaction->id,
                'paid_on' => $paidOn->toDateString(),
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            $remaining = max(0, (float) $debt->remaining_amount - (float) $data['amount']);

            $debt->forceFill([
                'remaining_amount' => $remaining,
                'status' => $remaining <= 0 ? 'paid' : 'active',
            ])->save();

            return $payment->load(['paymentAccount', 'transaction']);
        });
    }

    private function resolveExpenseCategoryId(User $user): ?int
    {
        return Category::query()
            ->where('type', 'expense')
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
            ->whereIn('slug', ['cicilan', 'tagihan'])
            ->orderByRaw("CASE WHEN slug = 'cicilan' THEN 0 ELSE 1 END")
            ->value('id');
    }
}
