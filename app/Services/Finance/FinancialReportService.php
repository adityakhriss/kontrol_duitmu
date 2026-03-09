<?php

namespace App\Services\Finance;

use App\Models\FinancialReport;
use App\Models\User;
use Carbon\CarbonImmutable;

class FinancialReportService
{
    public function __construct(private readonly FinancialAnalysisSnapshotService $financialAnalysisSnapshotService)
    {
    }

    public function eligibility(User $user): array
    {
        return $this->financialAnalysisSnapshotService->eligibility($user);
    }

    public function create(User $user, array $data): FinancialReport
    {
        $periodStart = CarbonImmutable::parse($data['period_start'])->startOfDay();
        $periodEnd = CarbonImmutable::parse($data['period_end'])->endOfDay();
        $snapshotResult = $this->financialAnalysisSnapshotService->getOrCreateMonthlyInsight($user);
        $latestInsight = $user->aiFinancialInsights()
            ->whereDate('snapshot_month', '<=', $periodEnd->startOfMonth()->toDateString())
            ->latest('snapshot_month')
            ->latest('id')
            ->first();

        $transactions = $user->transactions()
            ->with(['category', 'paymentAccount'])
            ->whereBetween('transaction_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->orderBy('transaction_date')
            ->get();

        $income = (float) $transactions->where('type', 'income')->sum('amount');
        $expense = (float) $transactions->where('type', 'expense')->sum('amount');
        $expenseByCategory = $transactions
            ->where('type', 'expense')
            ->groupBy(fn ($transaction) => $transaction->category?->name ?? 'Tanpa kategori')
            ->map(fn ($items, $category) => [
                'category' => $category,
                'amount' => (float) $items->sum('amount'),
                'count' => $items->count(),
            ])
            ->sortByDesc('amount')
            ->values()
            ->all();

        $payload = [
            'summary' => [
                'income' => $income,
                'expense' => $expense,
                'net_cashflow' => $income - $expense,
                'transaction_count' => $transactions->count(),
                'liquid_balance_total' => (float) $user->paymentAccounts()->sum('balance'),
                'upcoming_bills_total' => (float) $user->bills()->whereIn('status', ['unpaid', 'overdue'])->sum('amount'),
                'goal_saved_total' => (float) $user->savingGoals()->sum('current_amount'),
                'remaining_debt_total' => (float) $user->debts()->sum('remaining_amount'),
                'investment_value_total' => (float) $user->investments()->sum('current_value'),
            ],
            'expense_by_category' => $expenseByCategory,
            'transactions' => $transactions->take(25)->map(fn ($transaction) => [
                'date' => $transaction->transaction_date?->toDateString(),
                'type' => $transaction->type,
                'amount' => (float) $transaction->amount,
                'account' => $transaction->paymentAccount?->name,
                'category' => $transaction->category?->name,
                'notes' => $transaction->notes,
            ])->values()->all(),
            'ai_analysis' => [
                'snapshot_status' => $snapshotResult['status'] ?? null,
                'analysis' => $latestInsight?->analysis,
                'recommendations' => $latestInsight?->recommendations,
                'snapshot_month' => $latestInsight?->snapshot_month?->toDateString(),
                'provider' => $latestInsight?->provider,
                'model' => $latestInsight?->model,
            ],
        ];

        return FinancialReport::query()->create([
            'user_id' => $user->id,
            'ai_financial_insight_id' => $latestInsight?->id,
            'title' => $data['title'] ?: 'Laporan '.CarbonImmutable::parse($data['period_start'])->translatedFormat('d M Y').' - '.CarbonImmutable::parse($data['period_end'])->translatedFormat('d M Y'),
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'payload' => $payload,
            'generated_at' => now(),
        ]);
    }
}
