<?php

namespace App\Services\Finance;

use App\Models\AccountMutation;
use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AccountMutationService
{
    public function record(
        User $user,
        PaymentAccount $account,
        string $mutationType,
        string $direction,
        float $amount,
        float $balanceBefore,
        float $balanceAfter,
        Carbon $mutationDate,
        ?Model $source = null,
        ?string $description = null,
    ): AccountMutation {
        return AccountMutation::query()->create([
            'user_id' => $user->id,
            'payment_account_id' => $account->id,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'mutation_type' => $mutationType,
            'direction' => $direction,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'mutation_date' => $mutationDate,
            'description' => $description,
        ]);
    }
}
