<?php

namespace App\Services\Finance;

use App\Models\PaymentAccount;
use App\Models\SavingGoal;
use App\Models\SavingGoalHistory;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SavingGoalService
{
    public function __construct(private readonly PaymentAccountService $paymentAccountService)
    {
    }

    public function create(User $user, array $data): SavingGoal
    {
        return SavingGoal::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'target_amount' => $data['target_amount'],
            'current_amount' => 0,
            'target_date' => $data['target_date'] ?? null,
            'status' => 'active',
            'description' => $data['description'] ?? null,
        ]);
    }

    public function recordEntry(User $user, SavingGoal $goal, array $data): SavingGoalHistory
    {
        return DB::transaction(function () use ($user, $goal, $data): SavingGoalHistory {
            /** @var PaymentAccount $account */
            $account = PaymentAccount::query()->where('user_id', $user->id)->findOrFail($data['payment_account_id']);
            $amount = (float) $data['amount'];
            $entryType = $data['entry_type'];
            $currentAmount = (float) $goal->current_amount;
            $nextAmount = $entryType === 'deposit' ? $currentAmount + $amount : $currentAmount - $amount;

            if ($nextAmount < 0) {
                throw new InvalidArgumentException('Saldo goal tidak mencukupi untuk penarikan.');
            }

            $history = SavingGoalHistory::query()->create([
                'saving_goal_id' => $goal->id,
                'user_id' => $user->id,
                'payment_account_id' => $account->id,
                'entry_type' => $entryType,
                'amount' => $amount,
                'entry_date' => $data['entry_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $goal->forceFill([
                'current_amount' => $nextAmount,
                'status' => $nextAmount >= (float) $goal->target_amount ? 'completed' : 'active',
            ])->save();

            $this->paymentAccountService->applyMutation(
                user: $user,
                account: $account,
                mutationType: 'saving_goal',
                direction: $entryType === 'deposit' ? 'debit' : 'credit',
                amount: $amount,
                mutationDate: Carbon::parse($data['entry_date'])->setTime(now()->hour, now()->minute),
                source: $history,
                description: $data['notes'] ?? ($entryType === 'deposit' ? 'Setor goal '.$goal->name : 'Tarik dana goal '.$goal->name),
            );

            return $history->load('paymentAccount');
        });
    }
}
