<?php

namespace App\Services\Finance;

use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class PaymentAccountService
{
    public function __construct(private readonly AccountMutationService $mutationService)
    {
    }

    public function applyMutation(
        User $user,
        PaymentAccount $account,
        string $mutationType,
        string $direction,
        float $amount,
        Carbon $mutationDate,
        ?Model $source = null,
        ?string $description = null,
    ): PaymentAccount {
        $account->refresh();

        $balanceBefore = (float) $account->balance;
        $balanceAfter = $direction === 'credit'
            ? $balanceBefore + $amount
            : $balanceBefore - $amount;

        if ($balanceAfter < 0) {
            throw new InvalidArgumentException('Saldo akun tidak mencukupi.');
        }

        $account->forceFill(['balance' => $balanceAfter])->save();

        $this->mutationService->record(
            user: $user,
            account: $account,
            mutationType: $mutationType,
            direction: $direction,
            amount: $amount,
            balanceBefore: $balanceBefore,
            balanceAfter: $balanceAfter,
            mutationDate: $mutationDate,
            source: $source,
            description: $description,
        );

        return $account->refresh();
    }
}
