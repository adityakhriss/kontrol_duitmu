<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Services\Finance\TransactionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactionService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        $transactions = $user->transactions()
            ->with(['paymentAccount', 'category'])
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(12);

        return view('transactions.index', [
            'transactions' => $transactions,
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
            'categories' => Category::query()->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        return view('transactions.create', [
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
            'categories' => Category::query()->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))->orderBy('type')->orderBy('name')->get()->groupBy('type'),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $transaction = $this->transactionService->create($request->user(), $request->validated());

        return redirect()->route('transactions.index')->with('status', 'Transaksi '.$transaction->type.' berhasil disimpan.');
    }
}
