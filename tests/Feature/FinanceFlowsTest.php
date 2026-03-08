<?php

use App\Models\ApiConfig;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\PaymentAccount;
use App\Models\SavingGoal;
use App\Models\SavingGoalHistory;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Database\Seeders\CategorySeeder;

beforeEach(function () {
    app(CategorySeeder::class)->run();
});

test('user can store a transaction and account balance is updated', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $category = Category::query()->where('type', 'income')->firstOrFail();

    $response = $this->actingAs($user)->post(route('transactions.store'), [
        'transaction_date' => now()->toDateString(),
        'type' => 'income',
        'payment_account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 1000000,
        'notes' => 'Test pemasukan',
    ]);

    $response->assertRedirect(route('transactions.index'));

    expect(Transaction::query()->count())->toBe(1)
        ->and((float) $account->fresh()->balance)->toBe(1000000.0)
        ->and($account->mutations()->count())->toBe(1);
});

test('user can transfer between accounts and both balances are updated', function () {
    $user = User::factory()->create();
    $from = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $to = $user->paymentAccounts()->where('slug', 'e-wallet')->firstOrFail();

    $from->update(['balance' => 500000]);

    $response = $this->actingAs($user)->post(route('transfers.store'), [
        'transfer_date' => now()->toDateString(),
        'from_payment_account_id' => $from->id,
        'to_payment_account_id' => $to->id,
        'amount' => 150000,
        'notes' => 'Transfer test',
    ]);

    $response->assertRedirect(route('accounts.index'));

    expect(Transfer::query()->count())->toBe(1)
        ->and((float) $from->fresh()->balance)->toBe(350000.0)
        ->and((float) $to->fresh()->balance)->toBe(150000.0)
        ->and($user->paymentAccounts()->withCount('mutations')->get()->sum('mutations_count'))->toBe(2);
});

test('user can update payment account name and balance', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();

    $response = $this->actingAs($user)->patch(route('accounts.update', $account), [
        'name' => 'Bank Operasional',
        'balance' => 725000,
        'notes' => 'Sinkron saldo rekening',
    ]);

    $response->assertRedirect();

    expect($account->fresh()->name)->toBe('Bank Operasional')
        ->and((float) $account->fresh()->balance)->toBe(725000.0)
        ->and($account->mutations()->count())->toBe(1)
        ->and($account->mutations()->latest('id')->first()?->mutation_type)->toBe('adjustment');
});

test('admin can update integration settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->put(route('admin.api-settings.update'), [
        'base_url' => 'https://www.alphavantage.co/query',
        'api_key' => 'demo-key',
        'default_category' => 'market_news',
        'fetch_limit' => 12,
        'sync_interval_minutes' => 60,
        'is_active' => 1,
        'google_calendar_enabled' => 1,
        'google_default_reminder_minutes' => 30,
    ]);

    $response->assertRedirect();

    expect(ApiConfig::query()->where('provider', 'alpha_vantage')->first())->not->toBeNull()
        ->and(ApiConfig::query()->where('provider', 'alpha_vantage')->first()->is_active)->toBeTrue();
});

test('admin can create update and delete a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Operator Baru',
        'email' => 'operator@example.com',
        'password' => 'password123',
        'role' => 'user',
        'is_active' => 1,
    ])->assertRedirect(route('admin.users'));

    $managedUser = User::query()->where('email', 'operator@example.com')->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.users.update', $managedUser), [
        'name' => 'Operator Update',
        'email' => 'operator-updated@example.com',
        'password' => '',
        'role' => 'admin',
        'is_active' => 0,
    ])->assertRedirect(route('admin.users'));

    expect($managedUser->fresh()->name)->toBe('Operator Update')
        ->and($managedUser->fresh()->email)->toBe('operator-updated@example.com')
        ->and($managedUser->fresh()->role)->toBe('admin')
        ->and($managedUser->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $managedUser))
        ->assertRedirect(route('admin.users'));

    expect(User::query()->whereKey($managedUser->id)->exists())->toBeFalse();
});

test('user can pay a bill and it creates an expense transaction', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $account->update(['balance' => 800000]);

    $bill = Bill::query()->create([
        'user_id' => $user->id,
        'payment_account_id' => $account->id,
        'name' => 'Internet rumah',
        'category_name' => 'Tagihan',
        'amount' => 350000,
        'due_date' => now()->addDays(2)->toDateString(),
        'status' => 'unpaid',
        'is_recurring' => false,
    ]);

    $response = $this->actingAs($user)->post(route('bills.payments.store', $bill), [
        'payment_account_id' => $account->id,
        'paid_on' => now()->toDateString(),
        'amount' => 350000,
        'notes' => 'Bayar Internet rumah',
    ]);

    $response->assertRedirect(route('bills.index'));

    $transaction = Transaction::query()->latest('id')->first();

    expect(BillPayment::query()->count())->toBe(1)
        ->and($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe('expense')
        ->and((float) $transaction->amount)->toBe(350000.0)
        ->and((float) $account->fresh()->balance)->toBe(450000.0)
        ->and($bill->fresh()->status)->toBe('paid')
        ->and($bill->payments()->first()?->transaction_id)->toBe($transaction->id);
});

test('user can create and fund a saving goal', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $account->update(['balance' => 1000000]);

    $this->actingAs($user)->post(route('saving-goals.store'), [
        'name' => 'Dana darurat',
        'target_amount' => 5000000,
        'target_date' => now()->addMonths(6)->toDateString(),
        'description' => 'Cadangan kas',
    ])->assertRedirect(route('saving-goals.index'));

    $goal = SavingGoal::query()->firstOrFail();

    $this->actingAs($user)->post(route('saving-goals.entries.store', $goal), [
        'payment_account_id' => $account->id,
        'entry_type' => 'deposit',
        'entry_date' => now()->toDateString(),
        'amount' => 750000,
        'notes' => 'Setoran awal',
    ])->assertRedirect(route('saving-goals.index'));

    expect(SavingGoalHistory::query()->count())->toBe(1)
        ->and((float) $goal->fresh()->current_amount)->toBe(750000.0)
        ->and((float) $account->fresh()->balance)->toBe(250000.0);
});

test('user can create and pay down a debt', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $account->update(['balance' => 1200000]);

    $this->actingAs($user)->post(route('debts.store'), [
        'name' => 'Kartu kredit',
        'lender_name' => 'Bank ABC',
        'total_amount' => 3000000,
        'monthly_payment' => 500000,
        'interest_rate' => 2.5,
        'start_date' => now()->subMonth()->toDateString(),
        'due_date' => now()->addMonth()->toDateString(),
        'notes' => 'Tagihan kartu kredit',
    ])->assertRedirect(route('debts.index'));

    $debt = Debt::query()->firstOrFail();

    $this->actingAs($user)->post(route('debts.payments.store', $debt), [
        'payment_account_id' => $account->id,
        'paid_on' => now()->toDateString(),
        'amount' => 500000,
        'notes' => 'Bayar minimum',
    ])->assertRedirect(route('debts.index'));

    expect(DebtPayment::query()->count())->toBe(1)
        ->and((float) $debt->fresh()->remaining_amount)->toBe(2500000.0)
        ->and((float) $account->fresh()->balance)->toBe(700000.0);
});

test('user can create an investment and record a sell transaction', function () {
    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $account->update(['balance' => 5000000]);

    $this->actingAs($user)->post(route('investments.store'), [
        'name' => 'BBCA',
        'ticker' => 'BBCA',
        'type' => 'Saham',
        'platform' => 'Stockbit',
        'payment_account_id' => $account->id,
        'transaction_date' => now()->subDays(5)->toDateString(),
        'units' => 100,
        'buy_price' => 9000,
        'current_price' => 9500,
        'notes' => 'Pembelian awal',
    ])->assertRedirect(route('investments.index'));

    $investment = Investment::query()->firstOrFail();

    $this->actingAs($user)->post(route('investments.transactions.store', $investment), [
        'payment_account_id' => $account->id,
        'type' => 'sell',
        'transaction_date' => now()->toDateString(),
        'units' => 40,
        'price' => 10000,
        'current_price' => 10000,
        'notes' => 'Ambil profit',
    ])->assertRedirect(route('investments.index'));

    expect(InvestmentTransaction::query()->count())->toBe(2)
        ->and((float) $investment->fresh()->units)->toBe(60.0)
        ->and((float) $investment->fresh()->current_value)->toBe(600000.0)
        ->and((float) $account->fresh()->balance)->toBe(4500000.0);
});
