<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvestmentRequest;
use App\Http\Requests\StoreInvestmentTransactionRequest;
use App\Models\Investment;
use App\Services\Finance\InvestmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InvestmentController extends Controller
{
    public function __construct(private readonly InvestmentService $investmentService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('investments.index', [
            'investments' => $user->investments()->with('transactions.paymentAccount')->orderBy('type')->get(),
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreInvestmentRequest $request): RedirectResponse
    {
        try {
            $this->investmentService->create($request->user(), $request->validated());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'units' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('investments.index')->with('status', 'Aset investasi berhasil ditambahkan.');
    }

    public function storeTransaction(StoreInvestmentTransactionRequest $request, Investment $investment): RedirectResponse
    {
        try {
            $this->investmentService->recordTransaction($request->user(), $investment, $request->validated());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'units' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('investments.index')->with('status', 'Transaksi investasi berhasil dicatat.');
    }

    public function destroy(Investment $investment): RedirectResponse
    {
        abort_unless((int) $investment->user_id === (int) request()->user()->id, 403);

        $investment->delete();

        return redirect()->route('investments.index')->with('status', 'Aset investasi berhasil dihapus.');
    }
}
