<?php

namespace App\Services\Finance;

use App\Models\PaymentAccount;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(private readonly PaymentAccountService $paymentAccountService)
    {
    }

    public function create(User $user, array $data): Transfer
    {
        return DB::transaction(function () use ($user, $data): Transfer {
            /** @var PaymentAccount $fromAccount */
            $fromAccount = PaymentAccount::query()->where('user_id', $user->id)->findOrFail($data['from_payment_account_id']);
            /** @var PaymentAccount $toAccount */
            $toAccount = PaymentAccount::query()->where('user_id', $user->id)->findOrFail($data['to_payment_account_id']);

            $transfer = Transfer::query()->create([
                'user_id' => $user->id,
                'from_payment_account_id' => $fromAccount->id,
                'to_payment_account_id' => $toAccount->id,
                'transfer_date' => $data['transfer_date'],
                'amount' => $data['amount'],
                'notes' => $data['notes'] ?? null,
            ]);

            $transferDate = Carbon::parse($data['transfer_date'])->setTime(now()->hour, now()->minute);
            $amount = (float) $data['amount'];

            $this->paymentAccountService->applyMutation(
                user: $user,
                account: $fromAccount,
                mutationType: 'transfer',
                direction: 'debit',
                amount: $amount,
                mutationDate: $transferDate,
                source: $transfer,
                description: $data['notes'] ?? 'Transfer ke '.$toAccount->name,
            );

            $this->paymentAccountService->applyMutation(
                user: $user,
                account: $toAccount,
                mutationType: 'transfer',
                direction: 'credit',
                amount: $amount,
                mutationDate: $transferDate,
                source: $transfer,
                description: $data['notes'] ?? 'Transfer dari '.$fromAccount->name,
            );

            return $transfer->load(['fromAccount', 'toAccount']);
        });
    }
}
