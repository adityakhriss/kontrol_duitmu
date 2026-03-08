<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\GoogleCalendarConnection;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\PaymentAccount;
use App\Models\SavingGoal;
use App\Models\SavingGoalHistory;
use App\Models\User;
use App\Services\Finance\PaymentAccountService;
use App\Services\Finance\TransactionService;
use App\Services\Finance\TransferService;
use Illuminate\Database\Seeder;

class FinanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first();

        if (! $user) {
            return;
        }

        $incomeCategories = Category::query()->whereNull('user_id')->where('type', 'income')->get()->keyBy('name');
        $expenseCategories = Category::query()->whereNull('user_id')->where('type', 'expense')->get()->keyBy('name');
        $accounts = $user->paymentAccounts()->get()->keyBy('slug');

        $transactionService = app(TransactionService::class);
        $transferService = app(TransferService::class);
        $paymentAccountService = app(PaymentAccountService::class);

        if ($user->transactions()->count() === 0) {
            $transactionService->create($user, [
                'transaction_date' => now()->subDays(8)->toDateString(),
                'type' => 'income',
                'payment_account_id' => $accounts['bank']->id,
                'category_id' => $incomeCategories['Gaji']->id ?? null,
                'amount' => 6500000,
                'notes' => 'Gaji bulanan',
            ]);

            $transactionService->create($user, [
                'transaction_date' => now()->subDays(6)->toDateString(),
                'type' => 'income',
                'payment_account_id' => $accounts['bank']->id,
                'category_id' => $incomeCategories['Freelance']->id ?? null,
                'amount' => 1750000,
                'notes' => 'Pembayaran project freelance',
            ]);

            $transferService->create($user, [
                'transfer_date' => now()->subDays(5)->toDateString(),
                'from_payment_account_id' => $accounts['bank']->id,
                'to_payment_account_id' => $accounts['e-wallet']->id,
                'amount' => 500000,
                'notes' => 'Top up saldo harian',
            ]);

            $transferService->create($user, [
                'transfer_date' => now()->subDays(4)->toDateString(),
                'from_payment_account_id' => $accounts['bank']->id,
                'to_payment_account_id' => $accounts['tunai']->id,
                'amount' => 250000,
                'notes' => 'Ambil tunai',
            ]);

            foreach ([
                ['days' => 4, 'account' => 'e-wallet', 'category' => 'Makan', 'amount' => 95000, 'notes' => 'Makan siang dan kopi'],
                ['days' => 3, 'account' => 'bank', 'category' => 'Tagihan', 'amount' => 520000, 'notes' => 'Bayar listrik bulanan'],
                ['days' => 2, 'account' => 'tunai', 'category' => 'Transport', 'amount' => 150000, 'notes' => 'Transport dan parkir'],
                ['days' => 1, 'account' => 'e-wallet', 'category' => 'Belanja', 'amount' => 210000, 'notes' => 'Belanja kebutuhan mingguan'],
            ] as $expense) {
                $transactionService->create($user, [
                    'transaction_date' => now()->subDays($expense['days'])->toDateString(),
                    'type' => 'expense',
                    'payment_account_id' => $accounts[$expense['account']]->id,
                    'category_id' => $expenseCategories[$expense['category']]->id ?? null,
                    'amount' => $expense['amount'],
                    'notes' => $expense['notes'],
                ]);
            }
        }

        $bills = [
            ['name' => 'Internet rumah', 'category_name' => 'Internet', 'amount' => 385000, 'due_date' => now()->addDays(2), 'status' => 'unpaid', 'account' => 'bank'],
            ['name' => 'Listrik bulanan', 'category_name' => 'Utilitas', 'amount' => 520000, 'due_date' => now()->addDays(3), 'status' => 'unpaid', 'account' => 'bank'],
            ['name' => 'Cicilan laptop', 'category_name' => 'Cicilan', 'amount' => 1250000, 'due_date' => now()->addDays(5), 'status' => 'unpaid', 'account' => 'e-wallet'],
            ['name' => 'Langganan desain', 'category_name' => 'Langganan', 'amount' => 159000, 'due_date' => now()->subDays(4), 'status' => 'paid', 'account' => 'e-wallet'],
        ];

        foreach ($bills as $billData) {
            $bill = Bill::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $billData['name']],
                [
                    'payment_account_id' => $accounts[$billData['account']]->id,
                    'category_name' => $billData['category_name'],
                    'amount' => $billData['amount'],
                    'due_date' => $billData['due_date'],
                    'status' => $billData['status'],
                    'is_recurring' => true,
                    'recurring_period' => 'monthly',
                    'paid_at' => $billData['status'] === 'paid' ? now()->subDays(5) : null,
                ],
            );

            if ($billData['status'] === 'paid' && $bill->payments()->count() === 0) {
                $transaction = $user->transactions()->where('notes', 'Bayar '.$billData['name'])->first()
                    ?: $transactionService->create($user, [
                        'transaction_date' => now()->subDays(5)->toDateString(),
                        'type' => 'expense',
                        'payment_account_id' => $accounts[$billData['account']]->id,
                        'category_id' => $expenseCategories['Tagihan']->id ?? null,
                        'amount' => $billData['amount'],
                        'notes' => 'Bayar '.$billData['name'],
                    ]);

                BillPayment::query()->create([
                    'bill_id' => $bill->id,
                    'user_id' => $user->id,
                    'payment_account_id' => $accounts[$billData['account']]->id,
                    'transaction_id' => $transaction->id,
                    'paid_on' => now()->subDays(5)->toDateString(),
                    'amount' => $billData['amount'],
                    'notes' => 'Pembayaran otomatis seed',
                ]);
            }
        }

        foreach ([
            ['name' => 'Dana darurat', 'target_amount' => 20000000, 'current_amount' => 13600000, 'target_date' => now()->addMonths(4), 'status' => 'active'],
            ['name' => 'Liburan akhir tahun', 'target_amount' => 10000000, 'current_amount' => 4200000, 'target_date' => now()->addMonths(8), 'status' => 'active'],
            ['name' => 'Upgrade laptop', 'target_amount' => 15000000, 'current_amount' => 12450000, 'target_date' => now()->addMonths(2), 'status' => 'active'],
        ] as $goalData) {
            $goal = SavingGoal::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $goalData['name']],
                $goalData,
            );

            if ($goal->histories()->count() === 0) {
                SavingGoalHistory::query()->create([
                    'saving_goal_id' => $goal->id,
                    'user_id' => $user->id,
                    'payment_account_id' => $accounts['bank']->id,
                    'entry_type' => 'deposit',
                    'amount' => $goalData['current_amount'],
                    'entry_date' => now()->subMonth()->toDateString(),
                    'notes' => 'Seeded initial goal balance',
                ]);
            }
        }

        foreach ([
            ['name' => 'Cicilan laptop', 'lender_name' => 'Bank XYZ', 'total_amount' => 12500000, 'remaining_amount' => 6250000, 'monthly_payment' => 1250000, 'due_date' => now()->addDays(5)],
            ['name' => 'Kartu kredit', 'lender_name' => 'Bank ABC', 'total_amount' => 4500000, 'remaining_amount' => 2800000, 'monthly_payment' => 1200000, 'due_date' => now()->addDays(12)],
        ] as $debtData) {
            $debt = Debt::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $debtData['name']],
                array_merge($debtData, ['status' => 'active', 'start_date' => now()->subMonths(3)]),
            );

            if ($debt->payments()->count() === 0) {
                DebtPayment::query()->create([
                    'debt_id' => $debt->id,
                    'user_id' => $user->id,
                    'payment_account_id' => $accounts['bank']->id,
                    'paid_on' => now()->subMonth()->toDateString(),
                    'amount' => $debtData['monthly_payment'],
                    'notes' => 'Seeded debt payment',
                ]);
            }
        }

        foreach ([
            ['name' => 'BBCA', 'ticker' => 'BBCA', 'type' => 'Saham', 'units' => 100, 'buy_price' => 8950, 'current_price' => 9700, 'platform' => 'Stockbit'],
            ['name' => 'Reksa Dana Pasar Uang', 'ticker' => null, 'type' => 'Reksa Dana', 'units' => 1, 'buy_price' => 5400000, 'current_price' => 5400000, 'platform' => 'Bibit'],
            ['name' => 'Emas Digital', 'ticker' => null, 'type' => 'Emas', 'units' => 10, 'buy_price' => 620000, 'current_price' => 685000, 'platform' => 'Pluang'],
        ] as $investmentData) {
            $totalCost = $investmentData['units'] * $investmentData['buy_price'];
            $currentValue = $investmentData['units'] * $investmentData['current_price'];

            $investment = Investment::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $investmentData['name']],
                array_merge($investmentData, [
                    'total_cost' => $totalCost,
                    'current_value' => $currentValue,
                    'purchase_date' => now()->subMonths(4),
                ]),
            );

            if ($investment->transactions()->count() === 0) {
                InvestmentTransaction::query()->create([
                    'investment_id' => $investment->id,
                    'user_id' => $user->id,
                    'payment_account_id' => $accounts['bank']->id,
                    'type' => 'buy',
                    'transaction_date' => now()->subMonths(4)->toDateString(),
                    'units' => $investmentData['units'],
                    'price' => $investmentData['buy_price'],
                    'total_amount' => $totalCost,
                    'notes' => 'Seeded purchase',
                ]);
            }
        }

        GoogleCalendarConnection::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'google_email' => 'test.calendar@example.com',
                'calendar_id' => 'primary',
                'is_active' => false,
            ],
        );

        $paymentAccountService->adjustBalance($user, $accounts['bank'], 13800000, 'Sinkronisasi seed final bank');
        $paymentAccountService->adjustBalance($user, $accounts['tunai'], 1250000, 'Sinkronisasi seed final tunai');
        $paymentAccountService->adjustBalance($user, $accounts['e-wallet'], 3400000, 'Sinkronisasi seed final e-wallet');
    }
}
