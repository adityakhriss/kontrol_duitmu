<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransferRequest;
use App\Services\Finance\TransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TransferController extends Controller
{
    public function __construct(private readonly TransferService $transferService)
    {
    }

    public function create(): View
    {
        $user = request()->user();

        return view('accounts.transfer', [
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
            'recentTransfers' => $user->transfers()->with(['fromAccount', 'toAccount'])->latest('transfer_date')->take(5)->get(),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        $this->transferService->create($request->user(), $request->validated());

        return redirect()->route('accounts.index')->with('status', 'Transfer saldo berhasil diproses.');
    }
}
