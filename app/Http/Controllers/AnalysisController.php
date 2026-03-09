<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\Finance\FinancialAnalysisSnapshotService;
use App\Support\FinancePresenter;
use Illuminate\Contracts\View\View;

class AnalysisController extends Controller
{
    public function __construct(private readonly FinancialAnalysisSnapshotService $financialAnalysisSnapshotService)
    {
    }

    public function index(): View
    {
        $user = request()->user();
        $income = (float) $user->transactions()->where('type', 'income')->sum('amount');
        $expense = (float) $user->transactions()->where('type', 'expense')->sum('amount');
        $aiInsightResult = $this->financialAnalysisSnapshotService->getOrCreateMonthlyInsight($user);

        $categories = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->with('category')
            ->selectRaw('category_id, SUM(amount) total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $totalExpense = max(1, (float) $categories->sum('total'));

        return view('analysis.index', [
            'cashflow' => [
                'income' => FinancePresenter::money($income),
                'expense' => FinancePresenter::money($expense),
                'net' => FinancePresenter::signedMoney($income - $expense),
            ],
            'aiInsightResult' => $aiInsightResult,
            'categoryBreakdown' => $categories->map(fn ($row) => [
                'label' => $row->category?->name ?? 'Tanpa kategori',
                'value' => (int) round(((float) $row->total / $totalExpense) * 100),
            ]),
        ]);
    }
}
