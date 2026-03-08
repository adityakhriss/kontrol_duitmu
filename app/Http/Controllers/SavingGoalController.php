<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSavingGoalEntryRequest;
use App\Http\Requests\StoreSavingGoalRequest;
use App\Models\SavingGoal;
use App\Services\Finance\SavingGoalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SavingGoalController extends Controller
{
    public function __construct(private readonly SavingGoalService $savingGoalService)
    {
    }

    public function index(): View
    {
        $user = request()->user();

        return view('saving-goals.index', [
            'goals' => $user->savingGoals()->with('histories')->orderBy('target_date')->get(),
            'goalHistory' => $user->savingGoals()->with('histories.paymentAccount')->get()->pluck('histories')->flatten()->sortByDesc('entry_date')->take(8),
            'accounts' => $user->paymentAccounts()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreSavingGoalRequest $request): RedirectResponse
    {
        $this->savingGoalService->create($request->user(), $request->validated());

        return redirect()->route('saving-goals.index')->with('status', 'Goal tabungan berhasil dibuat.');
    }

    public function storeEntry(StoreSavingGoalEntryRequest $request, SavingGoal $savingGoal): RedirectResponse
    {
        try {
            $this->savingGoalService->recordEntry($request->user(), $savingGoal, $request->validated());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'amount' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('saving-goals.index')->with('status', 'Mutasi goal tabungan berhasil dicatat.');
    }

    public function destroy(SavingGoal $savingGoal): RedirectResponse
    {
        abort_unless((int) $savingGoal->user_id === (int) request()->user()->id, 403);

        $savingGoal->delete();

        return redirect()->route('saving-goals.index')->with('status', 'Goal tabungan berhasil dihapus.');
    }
}
