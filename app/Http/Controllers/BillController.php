<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillPaymentRequest;
use App\Http\Requests\StoreBillRequest;
use App\Models\Bill;
use App\Services\Finance\BillPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class BillController extends Controller
{
    public function __construct(private readonly BillPaymentService $billPaymentService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('bills.index', [
            'bills' => $user->bills()->with('paymentAccount')->orderBy('due_date')->get(),
            'paymentHistory' => $user->bills()->with('payments.bill', 'payments.paymentAccount')->get()->pluck('payments')->flatten()->sortByDesc('paid_on')->take(6),
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreBillRequest $request): RedirectResponse
    {
        $request->user()->bills()->create([
            'payment_account_id' => $request->input('payment_account_id'),
            'name' => $request->string('name')->toString(),
            'category_name' => $request->string('category_name')->toString(),
            'amount' => $request->input('amount'),
            'due_date' => $request->input('due_date'),
            'status' => 'unpaid',
            'is_recurring' => $request->boolean('is_recurring'),
            'recurring_period' => $request->boolean('is_recurring') ? $request->input('recurring_period', 'monthly') : null,
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('bills.index')->with('status', 'Tagihan berhasil ditambahkan.');
    }

    public function storePayment(StoreBillPaymentRequest $request, Bill $bill): RedirectResponse
    {
        try {
            $this->billPaymentService->create($request->user(), $bill, $request->validated());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'payment_account_id' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('bills.index')->with('status', 'Pembayaran tagihan berhasil dicatat.');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        abort_unless((int) $bill->user_id === (int) request()->user()->id, 403);

        $bill->delete();

        return redirect()->route('bills.index')->with('status', 'Tagihan berhasil dihapus.');
    }
}
