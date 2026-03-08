<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentNews;
use App\Models\Transaction;
use App\Support\FinancePresenter;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $accounts = $user->paymentAccounts()->orderBy('sort_order')->get();
        $periodStart = now()->startOfMonth();

        $income = (float) $user->transactions()->where('type', 'income')->whereDate('transaction_date', '>=', $periodStart)->sum('amount');
        $expense = (float) $user->transactions()->where('type', 'expense')->whereDate('transaction_date', '>=', $periodStart)->sum('amount');
        $totalLiquid = (float) $accounts->sum('balance');

        $upcomingBills = $user->bills()
            ->whereIn('status', ['unpaid', 'overdue'])
            ->orderBy('due_date')
            ->take(3)
            ->get();

        $goals = $user->savingGoals()->where('status', 'active')->take(3)->get();
        $investments = $user->investments()->latest()->take(4)->get();
        $news = InvestmentNews::query()->latest('published_at')->take(3)->get();
        $largestCategory = Transaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->selectRaw('category_id, SUM(amount) total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        return view('dashboard', [
            'summary' => [
                'total_liquid' => FinancePresenter::money($totalLiquid),
                'income' => FinancePresenter::money($income),
                'expense' => FinancePresenter::money($expense),
                'net_cashflow' => FinancePresenter::signedMoney($income - $expense),
                'active_bills' => $user->bills()->whereIn('status', ['unpaid', 'overdue'])->count(),
                'investment_value' => FinancePresenter::money((float) $user->investments()->sum('current_value')),
            ],
            'accounts' => $accounts,
            'upcomingBills' => $upcomingBills,
            'goals' => $goals,
            'news' => $news,
            'insights' => [
                'cashflow' => $expense <= $income
                    ? 'Cashflow bulan ini masih sehat dan memberi ruang untuk setor goal tambahan.'
                    : 'Pengeluaran bulan ini sudah melampaui pemasukan dan perlu dikendalikan.',
                'bill' => $upcomingBills->isNotEmpty()
                    ? $upcomingBills->count().' tagihan terdekat perlu diprioritaskan dari saldo utama.'
                    : 'Tidak ada tagihan mendesak dalam waktu dekat.',
                'wallet' => $accounts->sortByDesc('balance')->first()?->name
                    ? $accounts->sortByDesc('balance')->first()->name.' saat ini menjadi akun dengan saldo terbesar.'
                    : 'Belum ada akun aktif.',
                'category' => $largestCategory && $largestCategory->category
                    ? 'Kategori pengeluaran terbesar bulan ini adalah '.$largestCategory->category->name.'.'
                    : 'Belum ada cukup data kategori pengeluaran.',
            ],
            'investmentHighlights' => $investments,
        ]);
    }
}
