<?php

namespace App\Services\Finance;

use App\Models\Category;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\PaymentAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvestmentService
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function create(User $user, array $data): Investment
    {
        return DB::transaction(function () use ($user, $data): Investment {
            $units = (float) $data['units'];
            $buyPrice = (float) $data['buy_price'];
            $currentPrice = (float) ($data['current_price'] ?? $data['buy_price']);

            $investment = Investment::query()->create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'ticker' => $data['ticker'] ?? null,
                'type' => $data['type'],
                'units' => 0,
                'buy_price' => 0,
                'current_price' => $currentPrice,
                'total_cost' => 0,
                'current_value' => 0,
                'platform' => $data['platform'] ?? null,
                'purchase_date' => null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recordTransaction($user, $investment, [
                'payment_account_id' => $data['payment_account_id'],
                'type' => 'buy',
                'transaction_date' => $data['transaction_date'],
                'units' => $units,
                'price' => $buyPrice,
                'current_price' => $currentPrice,
                'notes' => $data['notes'] ?? 'Pembelian awal '.$data['name'],
            ], false);

            return $investment->fresh(['transactions']);
        });
    }

    public function recordTransaction(User $user, Investment $investment, array $data, bool $updatePurchaseDate = true): InvestmentTransaction
    {
        return DB::transaction(function () use ($user, $investment, $data, $updatePurchaseDate): InvestmentTransaction {
            /** @var PaymentAccount $account */
            $account = PaymentAccount::query()->where('user_id', $user->id)->findOrFail($data['payment_account_id']);
            $units = (float) $data['units'];
            $price = (float) $data['price'];
            $totalAmount = $units * $price;
            $type = $data['type'];
            $currentPrice = (float) ($data['current_price'] ?? $investment->current_price ?: $price);

            if ($type === 'sell' && $units > (float) $investment->units) {
                throw new InvalidArgumentException('Unit yang dijual melebihi kepemilikan saat ini.');
            }

            $transactionDate = Carbon::parse($data['transaction_date']);

            $this->transactionService->create($user, [
                'transaction_date' => $transactionDate->toDateString(),
                'type' => $type === 'buy' ? 'expense' : 'income',
                'payment_account_id' => $account->id,
                'category_id' => $type === 'buy' ? $this->resolveInvestmentCategoryId($user) : null,
                'amount' => $totalAmount,
                'notes' => $data['notes'] ?? ($type === 'buy' ? 'Beli '.$investment->name : 'Jual '.$investment->name),
            ]);

            $entry = InvestmentTransaction::query()->create([
                'investment_id' => $investment->id,
                'user_id' => $user->id,
                'payment_account_id' => $account->id,
                'type' => $type,
                'transaction_date' => $transactionDate->toDateString(),
                'units' => $units,
                'price' => $price,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
            ]);

            $currentUnits = (float) $investment->units;
            $currentCost = (float) $investment->total_cost;

            if ($type === 'buy') {
                $newUnits = $currentUnits + $units;
                $newCost = $currentCost + $totalAmount;
            } else {
                $averageCost = $currentUnits > 0 ? $currentCost / $currentUnits : 0;
                $newUnits = $currentUnits - $units;
                $newCost = max(0, $currentCost - ($averageCost * $units));
            }

            $investment->forceFill([
                'units' => $newUnits,
                'buy_price' => $newUnits > 0 ? $newCost / $newUnits : 0,
                'current_price' => $currentPrice,
                'total_cost' => $newCost,
                'current_value' => $newUnits * $currentPrice,
                'purchase_date' => $updatePurchaseDate && ! $investment->purchase_date ? $transactionDate->toDateString() : $investment->purchase_date,
            ])->save();

            return $entry->load('paymentAccount');
        });
    }

    private function resolveInvestmentCategoryId(User $user): ?int
    {
        return Category::query()
            ->where('type', 'expense')
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
            ->where('slug', 'investasi')
            ->value('id');
    }
}
