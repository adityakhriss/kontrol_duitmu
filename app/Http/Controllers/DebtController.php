<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDebtPaymentRequest;
use App\Http\Requests\StoreDebtRequest;
use App\Models\Debt;
use App\Services\Finance\DebtService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DebtController extends Controller
{
    public function __construct(private readonly DebtService $debtService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('debts.index', [
            'debts' => $user->debts()->with('payments.paymentAccount')->orderBy('due_date')->get(),
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreDebtRequest $request): RedirectResponse
    {
        $this->debtService->create($request->user(), $request->validated());

        return redirect()->route('debts.index')->with('status', 'Data hutang berhasil ditambahkan.');
    }

    public function storePayment(StoreDebtPaymentRequest $request, Debt $debt): RedirectResponse
    {
        try {
            $this->debtService->recordPayment($request->user(), $debt, $request->validated());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('debts.index')->with('status', 'Pembayaran hutang berhasil dicatat.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        abort_unless((int) $debt->user_id === (int) request()->user()->id, 403);

        $debt->delete();

        return redirect()->route('debts.index')->with('status', 'Data hutang berhasil dihapus.');
    }
}
