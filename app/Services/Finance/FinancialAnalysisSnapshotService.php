<?php

namespace App\Services\Finance;

use App\Models\AiFinancialInsight;
use App\Models\ApiSyncLog;
use App\Models\User;
use App\Services\Integrations\AiProviderManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FinancialAnalysisSnapshotService
{
    public function __construct(private readonly AiProviderManager $aiProviderManager)
    {
    }

    public function getOrCreateMonthlyInsight(User $user): array
    {
        $snapshotMonth = now()->startOfMonth()->toDateString();
        $existing = $user->aiFinancialInsights()->whereDate('snapshot_month', $snapshotMonth)->latest('id')->first();

        if ($existing) {
            return [
                'status' => 'existing',
                'insight' => $existing,
                'eligibility' => $this->eligibility($user),
            ];
        }

        $eligibility = $this->eligibility($user);

        if (! $eligibility['eligible']) {
            return [
                'status' => 'ineligible',
                'eligibility' => $eligibility,
                'insight' => null,
            ];
        }

        if (! $this->aiProviderManager->isReady()) {
            return [
                'status' => 'unavailable',
                'eligibility' => $eligibility,
                'insight' => null,
                'provider_label' => $this->aiProviderManager->currentProvider()->providerLabel(),
            ];
        }

        $summary = $this->buildSummary($user);

        $provider = $this->aiProviderManager->currentProvider();
        $log = ApiSyncLog::query()->create([
            'provider' => $provider->providerKey(),
            'status' => 'running',
            'action' => 'generate_financial_insight',
            'message' => 'Generate insight keuangan bulanan dimulai',
            'started_at' => now(),
        ]);

        try {
            $result = $this->aiProviderManager->analyzeFinancialSummary($summary);

            $insight = AiFinancialInsight::query()->create([
                'user_id' => $user->id,
                'provider' => $provider->providerKey(),
                'model' => data_get($provider->config()->settings, 'model'),
                'snapshot_month' => $snapshotMonth,
                'analysis_period_start' => $summary['meta']['period_start'],
                'analysis_period_end' => $summary['meta']['period_end'],
                'status' => 'generated',
                'analysis' => implode("\n", (array) ($result['analysis'] ?? [])),
                'recommendations' => implode("\n", (array) ($result['recommendations'] ?? [])),
                'input_summary' => $summary,
                'output_payload' => $result,
                'generated_at' => now(),
            ]);

            $log->update([
                'status' => 'success',
                'message' => 'Insight '.$provider->providerLabel().' berhasil dibuat.',
                'records_count' => 1,
                'finished_at' => now(),
            ]);

            return [
                'status' => 'generated',
                'insight' => $insight,
                'eligibility' => $eligibility,
            ];
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            return [
                'status' => 'failed',
                'insight' => null,
                'eligibility' => $eligibility,
                'message' => $exception->getMessage(),
            ];
        }
    }

    public function eligibility(User $user): array
    {
        $firstTransactionDate = $user->transactions()->min('transaction_date');
        $transactionCount = $user->transactions()->count();

        if (! $firstTransactionDate) {
            return [
                'eligible' => false,
                'reason' => 'Belum ada transaksi yang cukup untuk dianalisis AI.',
                'transaction_count' => 0,
                'months_available' => 0,
                'available_on' => null,
            ];
        }

        $first = CarbonImmutable::parse($firstTransactionDate)->startOfDay();
        $availableOn = $first->addMonth();
        $monthsAvailable = $first->diffInDays(now()->startOfDay()) >= 30 ? max(1, $first->diffInMonths(now())) : 0;

        if ($first->diffInDays(now()->startOfDay()) < 30) {
            return [
                'eligible' => false,
                'reason' => 'Insight AI otomatis aktif setelah 1 bulan sejak transaksi pertama user.',
                'transaction_count' => $transactionCount,
                'months_available' => $monthsAvailable,
                'available_on' => $availableOn->toDateString(),
            ];
        }

        return [
            'eligible' => true,
            'reason' => null,
            'transaction_count' => $transactionCount,
            'months_available' => $monthsAvailable,
            'available_on' => $availableOn->toDateString(),
        ];
    }

    public function buildSummary(User $user): array
    {
        $periodEnd = now()->toDateString();
        $periodStart = now()->subDays(29)->toDateString();
        $periodStartDate = CarbonImmutable::parse($periodStart);
        $previousPeriodStart = $periodStartDate->subDays(30)->toDateString();
        $previousPeriodEnd = $periodStartDate->subDay()->toDateString();
        $monthsAvailable = $this->eligibility($user)['months_available'];

        $currentTransactions = $user->transactions()
            ->with(['category', 'paymentAccount'])
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->orderBy('transaction_date')
            ->get();

        $previousTransactions = $user->transactions()
            ->whereBetween('transaction_date', [$previousPeriodStart, $previousPeriodEnd])
            ->get();

        $currentIncome = (float) $currentTransactions->where('type', 'income')->sum('amount');
        $currentExpense = (float) $currentTransactions->where('type', 'expense')->sum('amount');
        $previousIncome = (float) $previousTransactions->where('type', 'income')->sum('amount');
        $previousExpense = (float) $previousTransactions->where('type', 'expense')->sum('amount');

        $expenseByCategory = $currentTransactions
            ->where('type', 'expense')
            ->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Tanpa kategori')
            ->map(fn (Collection $items, string $category) => [
                'category' => $category,
                'amount' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ])
            ->sortByDesc('amount')
            ->values()
            ->take(8)
            ->all();

        $accounts = $user->paymentAccounts()->orderBy('sort_order')->get(['name', 'type', 'balance']);
        $bills = $user->bills()->whereIn('status', ['unpaid', 'overdue'])->orderBy('due_date')->take(10)->get(['name', 'amount', 'due_date', 'status']);
        $goals = $user->savingGoals()->get(['name', 'target_amount', 'current_amount', 'status', 'target_date']);
        $debts = $user->debts()->get(['name', 'remaining_amount', 'monthly_payment', 'status', 'due_date']);
        $investments = $user->investments()->get(['name', 'type', 'current_value', 'total_cost', 'market_change_percent', 'market_status']);

        $monthlyComparisons = $monthsAvailable >= 2
            ? $this->buildMonthlyComparisons($user)
            : [];

        return [
            'meta' => [
                'user_name' => $user->name,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'months_available' => $monthsAvailable,
                'transaction_count' => $currentTransactions->count(),
            ],
            'summary' => [
                'income' => $currentIncome,
                'expense' => $currentExpense,
                'net_cashflow' => $currentIncome - $currentExpense,
                'previous_income' => $previousIncome,
                'previous_expense' => $previousExpense,
                'previous_net_cashflow' => $previousIncome - $previousExpense,
                'liquid_balance_total' => (float) $accounts->sum('balance'),
                'upcoming_bills_total' => (float) $bills->sum('amount'),
                'active_goals_total' => (float) $goals->where('status', 'active')->sum('target_amount'),
                'goal_saved_total' => (float) $goals->sum('current_amount'),
                'remaining_debt_total' => (float) $debts->sum('remaining_amount'),
                'investment_value_total' => (float) $investments->sum('current_value'),
            ],
            'expense_by_category' => $expenseByCategory,
            'top_transactions' => $currentTransactions
                ->sortByDesc('amount')
                ->take(10)
                ->map(fn ($transaction) => [
                    'date' => $transaction->transaction_date?->toDateString(),
                    'type' => $transaction->type,
                    'amount' => (float) $transaction->amount,
                    'account' => $transaction->paymentAccount?->name,
                    'category' => $transaction->category?->name,
                    'notes' => $transaction->notes,
                ])
                ->values()
                ->all(),
            'accounts' => $accounts->map(fn ($account) => [
                'name' => $account->name,
                'type' => $account->type,
                'balance' => (float) $account->balance,
            ])->all(),
            'upcoming_bills' => $bills->map(fn ($bill) => [
                'name' => $bill->name,
                'amount' => (float) $bill->amount,
                'due_date' => $bill->due_date,
                'status' => $bill->status,
            ])->all(),
            'saving_goals' => $goals->map(fn ($goal) => [
                'name' => $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'status' => $goal->status,
                'target_date' => optional($goal->target_date)->toDateString(),
            ])->all(),
            'debts' => $debts->map(fn ($debt) => [
                'name' => $debt->name,
                'remaining_amount' => (float) $debt->remaining_amount,
                'monthly_payment' => (float) $debt->monthly_payment,
                'status' => $debt->status,
                'due_date' => optional($debt->due_date)->toDateString(),
            ])->all(),
            'investments' => $investments->map(fn ($investment) => [
                'name' => $investment->name,
                'type' => $investment->type,
                'current_value' => (float) $investment->current_value,
                'total_cost' => (float) $investment->total_cost,
                'market_change_percent' => (float) ($investment->market_change_percent ?? 0),
                'market_status' => $investment->market_status,
            ])->all(),
            'monthly_comparisons' => $monthlyComparisons,
        ];
    }

    protected function buildMonthlyComparisons(User $user): array
    {
        return $user->transactions()
            ->orderBy('transaction_date')
            ->get(['transaction_date', 'type', 'amount'])
            ->groupBy(fn ($transaction) => $transaction->transaction_date?->format('Y-m'))
            ->map(fn (Collection $items, string $month) => [
                'month' => $month,
                'income' => (float) $items->where('type', 'income')->sum('amount'),
                'expense' => (float) $items->where('type', 'expense')->sum('amount'),
                'net' => (float) $items->where('type', 'income')->sum('amount') - (float) $items->where('type', 'expense')->sum('amount'),
            ])
            ->sortBy('month')
            ->take(-3)
            ->values()
            ->all();
    }
}
