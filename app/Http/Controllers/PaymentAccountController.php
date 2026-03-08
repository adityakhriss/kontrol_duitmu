<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePaymentAccountRequest;
use App\Models\AccountMutation;
use App\Models\PaymentAccount;
use App\Services\Finance\PaymentAccountService;
use App\Support\FinancePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PaymentAccountController extends Controller
{
    public function __construct(private readonly PaymentAccountService $paymentAccountService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('accounts.index', [
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
            'recentMutations' => AccountMutation::query()->where('user_id', $user->id)->with('paymentAccount')->latest('mutation_date')->take(8)->get(),
            'totalBalance' => FinancePresenter::money((float) $user->paymentAccounts()->sum('balance')),
        ]);
    }

    public function update(UpdatePaymentAccountRequest $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        abort_unless($paymentAccount->user_id === $request->user()->id, 403);

        $paymentAccount->update(['name' => $request->string('name')->toString()]);
        $this->paymentAccountService->adjustBalance($request->user(), $paymentAccount, (float) $request->input('balance'), $request->input('notes'));

        return back()->with('status', 'Akun saldo berhasil diperbarui.');
    }
}
