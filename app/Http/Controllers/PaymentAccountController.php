<?php

namespace App\Http\Controllers;

use App\Models\AccountMutation;
use App\Support\FinancePresenter;
use Illuminate\Contracts\View\View;

class PaymentAccountController extends Controller
{
    public function index(): View
    {
        $user = request()->user();

        return view('accounts.index', [
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
            'recentMutations' => AccountMutation::query()->where('user_id', $user->id)->with('paymentAccount')->latest('mutation_date')->take(8)->get(),
            'totalBalance' => FinancePresenter::money((float) $user->paymentAccounts()->sum('balance')),
        ]);
    }
}
