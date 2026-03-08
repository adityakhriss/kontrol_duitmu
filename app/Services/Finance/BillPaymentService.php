<?php

namespace App\Services\Finance;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Category;
use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BillPaymentService
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function create(User $user, Bill $bill, array $data): BillPayment
    {
        return DB::transaction(function () use ($user, $bill, $data): BillPayment {
            /** @var PaymentAccount $account */
            $account = PaymentAccount::query()
                ->where('user_id', $user->id)
                ->findOrFail($data['payment_account_id']);

            $paidOn = Carbon::parse($data['paid_on']);
            $transaction = $this->transactionService->create($user, [
                'transaction_date' => $paidOn->toDateString(),
                'type' => 'expense',
                'payment_account_id' => $account->id,
                'category_id' => $this->resolveCategoryId($user, $bill),
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? 'Bayar '.$bill->name,
            ]);

            $payment = BillPayment::query()->create([
                'bill_id' => $bill->id,
                'user_id' => $user->id,
                'payment_account_id' => $account->id,
                'transaction_id' => $transaction->id,
                'paid_on' => $paidOn->toDateString(),
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            $bill->forceFill([
                'payment_account_id' => $account->id,
                'status' => $bill->is_recurring ? 'unpaid' : 'paid',
                'paid_at' => $paidOn->copy()->startOfDay(),
                'last_generated_at' => $bill->is_recurring ? $paidOn->copy()->startOfDay() : $bill->last_generated_at,
                'due_date' => $bill->is_recurring
                    ? $this->nextDueDate($bill->due_date, $bill->recurring_period)
                    : $bill->due_date,
            ])->save();

            return $payment->load(['bill', 'paymentAccount', 'transaction']);
        });
    }

    private function resolveCategoryId(User $user, Bill $bill): ?int
    {
        $category = Category::query()
            ->where('type', 'expense')
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
            ->where(function ($query) use ($bill) {
                $query
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($bill->category_name)])
                    ->orWhere('slug', str($bill->category_name)->slug()->value())
                    ->orWhere('slug', 'tagihan');
            })
            ->orderByRaw("CASE WHEN slug = ? THEN 0 WHEN LOWER(name) = ? THEN 1 ELSE 2 END", [
                str($bill->category_name)->slug()->value(),
                mb_strtolower($bill->category_name),
            ])
            ->first();

        return $category?->id;
    }

    private function nextDueDate(Carbon|string|null $date, ?string $period): Carbon
    {
        $dueDate = $date instanceof Carbon ? $date->copy() : Carbon::parse($date ?? now());

        return match ($period) {
            'daily' => $dueDate->addDay(),
            'weekly' => $dueDate->addWeek(),
            'yearly' => $dueDate->addYear(),
            default => $dueDate->addMonth(),
        };
    }
}
