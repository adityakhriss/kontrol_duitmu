<?php

use App\Models\ApiConfig;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Category;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\FinancialReport;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\InvestmentNews;
use App\Models\AiFinancialInsight;
use App\Models\PaymentAccount;
use App\Models\SavingGoal;
use App\Models\SavingGoalHistory;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Services\Integrations\RssInvestmentNewsService;
use App\Services\Integrations\YahooFinanceService;
use Database\Seeders\CategorySeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

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

test('admin can update integration settings', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->put(route('admin.api-settings.update'), [
        'rss_default_category' => 'idx_news',
        'rss_fetch_limit' => 12,
        'rss_sync_interval_minutes' => 60,
        'rss_sources' => "https://www.cnbcindonesia.com/market/rss\nhttps://www.antaranews.com/rss/ekonomi.xml",
        'rss_enabled' => 1,
        'yahoo_finance_base_url' => 'https://query1.finance.yahoo.com',
        'yahoo_finance_sync_interval_minutes' => 15,
        'yahoo_finance_chart_interval' => '1d',
        'yahoo_finance_chart_points' => 30,
        'yahoo_finance_enabled' => 1,
        'ai_default_provider' => 'openrouter',
        'openrouter_base_url' => 'https://openrouter.ai/api/v1',
        'openrouter_api_key' => 'openrouter-demo-key',
        'openrouter_model' => 'openrouter/auto',
        'openrouter_enabled' => 1,
        'google_calendar_enabled' => 1,
        'google_default_reminder_minutes' => 30,
    ]);

    $response->assertRedirect();

    expect(ApiConfig::query()->where('provider', 'rss_news')->first())->not->toBeNull()
        ->and(ApiConfig::query()->where('provider', 'rss_news')->first()->is_active)->toBeTrue()
        ->and(ApiConfig::query()->where('provider', 'yahoo_finance')->first())->not->toBeNull()
        ->and(ApiConfig::query()->where('provider', 'yahoo_finance')->first()->is_active)->toBeTrue()
        ->and(ApiConfig::query()->where('provider', 'openrouter')->first())->not->toBeNull()
        ->and(ApiConfig::query()->where('provider', 'openrouter')->first()->is_active)->toBeTrue();
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
        'market_symbol' => 'BBCA',
        'market_exchange' => 'IDX',
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
        ->and((float) $investment->fresh()->current_value)->toBe(540000.0)
        ->and((float) $account->fresh()->balance)->toBe(4500000.0);
});

test('market tracked investment can sync live market data from yahoo finance', function () {
    Http::fake([
        'https://query1.finance.yahoo.com/v8/finance/chart/BBCA.JK*' => Http::response([
            'chart' => [
                'result' => [[
                    'meta' => [
                        'symbol' => 'BBCA.JK',
                        'regularMarketPrice' => 10250,
                        'previousClose' => 10000,
                    ],
                    'timestamp' => [1773014400, 1773100800, 1773187200],
                    'indicators' => [
                        'quote' => [[
                            'close' => [9800, 9950, 10250],
                        ]],
                    ],
                ]],
            ],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'yahoo_finance'],
        [
            'base_url' => 'https://query1.finance.yahoo.com',
            'is_active' => true,
            'fetch_limit' => 30,
            'sync_interval_minutes' => 15,
            'settings' => ['chart_interval' => '1d', 'chart_points' => 30],
        ],
    );

    $user = User::factory()->create();
    $investment = Investment::query()->create([
        'user_id' => $user->id,
        'name' => 'BBCA',
        'ticker' => 'BBCA',
        'market_symbol' => 'BBCA',
        'market_exchange' => 'IDX',
        'market_provider' => 'yahoo_finance',
        'type' => 'Saham',
        'units' => 100,
        'buy_price' => 9000,
        'current_price' => 9000,
        'total_cost' => 900000,
        'current_value' => 900000,
        'market_status' => 'pending',
    ]);

    $result = app(YahooFinanceService::class)->syncInvestment($investment, false);

    expect($result['status'])->toBe('success')
        ->and((float) $investment->fresh()->current_price)->toBe(10250.0)
        ->and((float) $investment->fresh()->current_value)->toBe(1025000.0)
        ->and((float) $investment->fresh()->market_change_amount)->toBe(125000.0)
        ->and(round((float) $investment->fresh()->market_change_percent, 2))->toBe(13.89)
        ->and($investment->fresh()->market_status)->toBe('live');
});

test('admin can test yahoo finance connection from integration settings', function () {
    Http::fake([
        'https://query1.finance.yahoo.com/v8/finance/chart/AAPL*' => Http::response([
            'chart' => [
                'result' => [[
                    'meta' => [
                        'symbol' => 'AAPL',
                        'regularMarketPrice' => 245.12,
                        'previousClose' => 244.10,
                    ],
                    'timestamp' => [1773014400, 1773100800],
                    'indicators' => [
                        'quote' => [[
                            'close' => [244.10, 245.12],
                        ]],
                    ],
                ]],
            ],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'yahoo_finance'],
        [
            'base_url' => 'https://query1.finance.yahoo.com',
            'is_active' => true,
            'fetch_limit' => 30,
            'sync_interval_minutes' => 15,
            'settings' => ['chart_interval' => '1d', 'chart_points' => 30],
        ],
    );

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.api-settings.test-yahoo-finance'))
        ->assertRedirect();
});

test('admin can test active ai provider connection from integration settings', function () {
    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_title' => 'Analisis siap',
                        'analysis' => ['Cashflow stabil.'],
                        'recommendations' => ['Jaga rasio tabungan.'],
                    ]),
                ],
            ]],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'openrouter'],
        [
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key' => 'demo-key',
            'is_active' => true,
            'settings' => ['model' => 'openrouter/auto'],
        ],
    );

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.api-settings.test-ai-provider'))
        ->assertRedirect();
});

test('rss news sync stores headline summary image and link', function () {
    Http::fake([
        'https://www.cnbcindonesia.com/market/rss' => Http::response(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title>Market - CNBC Indonesia</title>
    <item>
      <guid>https://www.cnbcindonesia.com/market/test-1</guid>
      <pubDate>Mon, 09 Mar 2026 09:18:04 +0700</pubDate>
      <title><![CDATA[IHSG Menguat di Sesi Pagi]]></title>
      <description><![CDATA[<img src="https://cdn.example.com/ihsg.jpg"/> IHSG menguat setelah saham perbankan memimpin kenaikan.]]></description>
      <content:encoded><![CDATA[IHSG menguat setelah saham perbankan memimpin kenaikan di sesi pagi perdagangan.]]></content:encoded>
      <link>https://www.cnbcindonesia.com/market/test-1</link>
    </item>
  </channel>
</rss>
XML),
        'https://www.antaranews.com/rss/ekonomi.xml' => Http::response(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
    <title>Ekonomi - ANTARA News</title>
    <item>
      <guid>https://www.antaranews.com/berita/test-2</guid>
      <pubDate>Mon, 09 Mar 2026 10:00:00 +0700</pubDate>
      <title>Bank besar jadi penopang indeks</title>
      <description><![CDATA[Perbankan besar menopang indeks pada perdagangan hari ini.]]></description>
      <media:content url="https://cdn.example.com/bank.jpg" medium="image" />
      <link>https://www.antaranews.com/berita/test-2</link>
    </item>
  </channel>
</rss>
XML),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'rss_news'],
        [
            'is_active' => true,
            'default_category' => 'idx_news',
            'fetch_limit' => 10,
            'sync_interval_minutes' => 60,
            'settings' => [
                'sources' => [
                    'https://www.cnbcindonesia.com/market/rss',
                    'https://www.antaranews.com/rss/ekonomi.xml',
                ],
            ],
        ],
    );

    $result = app(RssInvestmentNewsService::class)->sync(true);

    expect($result['status'])->toBe('success')
        ->and(InvestmentNews::query()->count())->toBe(2)
        ->and(InvestmentNews::query()->where('title', 'IHSG Menguat di Sesi Pagi')->first())->not->toBeNull()
        ->and(InvestmentNews::query()->where('title', 'IHSG Menguat di Sesi Pagi')->first()->image_url)->toBe('https://cdn.example.com/ihsg.jpg')
        ->and(InvestmentNews::query()->where('title', 'Bank besar jadi penopang indeks')->first()->image_url)->toBe('https://cdn.example.com/bank.jpg');
});

test('user gets monthly ai financial insight snapshot only once after one month from first transaction', function () {
    Carbon::setTestNow('2026-03-20 09:00:00');

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_title' => 'Kondisi cukup sehat',
                        'analysis' => [
                            'Cashflow 30 hari terakhir masih positif.',
                            'Pengeluaran makan menjadi kategori dominan.',
                        ],
                        'recommendations' => [
                            'Batasi pengeluaran makan di luar rumah.',
                            'Sisihkan saldo ke dana darurat minggu ini.',
                        ],
                    ]),
                ],
            ]],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'openrouter'],
        [
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key' => 'demo-key',
            'is_active' => true,
            'settings' => ['model' => 'openrouter/auto'],
        ],
    );

    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $incomeCategory = Category::query()->where('type', 'income')->firstOrFail();
    $expenseCategory = Category::query()->where('type', 'expense')->firstOrFail();

    foreach (range(0, 34) as $day) {
        $date = now()->subDays($day)->toDateString();

        $user->transactions()->create([
            'payment_account_id' => $account->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'transaction_date' => $date,
            'amount' => 200000,
            'notes' => 'Income '.$day,
        ]);

        $user->transactions()->create([
            'payment_account_id' => $account->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'transaction_date' => $date,
            'amount' => 100000,
            'notes' => 'Expense '.$day,
        ]);
    }

    $this->actingAs($user)->get(route('analysis.index'))->assertOk();
    $this->actingAs($user)->get(route('analysis.index'))->assertOk();

    expect(AiFinancialInsight::query()->count())->toBe(1)
        ->and(AiFinancialInsight::query()->first()->snapshot_month->toDateString())->toBe('2026-03-01');

    Carbon::setTestNow();
});

test('monthly ai insight command generates snapshots only after one month from first transaction', function () {
    Carbon::setTestNow('2026-04-01 01:05:00');

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_title' => 'Snapshot bulanan',
                        'analysis' => ['Cashflow tercatat.'],
                        'recommendations' => ['Jaga konsistensi pencatatan.'],
                    ]),
                ],
            ]],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'openrouter'],
        [
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key' => 'demo-key',
            'is_active' => true,
            'settings' => ['model' => 'openrouter/auto'],
        ],
    );

    Setting::query()->updateOrCreate(
        ['key' => 'ai.provider'],
        ['group' => 'ai', 'value' => ['provider' => 'openrouter']],
    );

    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $incomeCategory = Category::query()->where('type', 'income')->firstOrFail();

    $user->transactions()->create([
        'payment_account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'type' => 'income',
        'transaction_date' => now()->subDays(31)->toDateString(),
        'amount' => 500000,
        'notes' => 'Transaksi awal',
    ]);

    $this->artisan('finance:generate-monthly-ai-insights')
        ->expectsOutputToContain('Generated: 1')
        ->assertExitCode(0);

    expect(AiFinancialInsight::query()->count())->toBe(1)
        ->and(AiFinancialInsight::query()->first()->snapshot_month->toDateString())->toBe('2026-04-01');

    Carbon::setTestNow();
});

test('user cannot pull financial report before one month from first transaction', function () {
    Carbon::setTestNow('2026-04-01 09:00:00');

    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $incomeCategory = Category::query()->where('type', 'income')->firstOrFail();

    $user->transactions()->create([
        'payment_account_id' => $account->id,
        'category_id' => $incomeCategory->id,
        'type' => 'income',
        'transaction_date' => now()->subDays(10)->toDateString(),
        'amount' => 500000,
        'notes' => 'Transaksi awal',
    ]);

    $this->actingAs($user)->post(route('reports.store'), [
        'title' => 'Laporan awal',
        'period_start' => now()->subDays(10)->toDateString(),
        'period_end' => now()->toDateString(),
    ])->assertSessionHasErrors('report');

    expect(FinancialReport::query()->count())->toBe(0);

    Carbon::setTestNow();
});

test('eligible user can pull financial report with ai analysis', function () {
    Carbon::setTestNow('2026-05-05 10:00:00');

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'summary_title' => 'Laporan sehat',
                        'analysis' => ['Cashflow masih positif dan stabil.'],
                        'recommendations' => ['Pertahankan disiplin cashflow.'],
                    ]),
                ],
            ]],
        ]),
    ]);

    ApiConfig::query()->updateOrCreate(
        ['provider' => 'openrouter'],
        [
            'base_url' => 'https://openrouter.ai/api/v1',
            'api_key' => 'demo-key',
            'is_active' => true,
            'settings' => ['model' => 'openrouter/auto'],
        ],
    );

    Setting::query()->updateOrCreate(
        ['key' => 'ai.provider'],
        ['group' => 'ai', 'value' => ['provider' => 'openrouter']],
    );

    $user = User::factory()->create();
    $account = $user->paymentAccounts()->where('slug', 'bank')->firstOrFail();
    $incomeCategory = Category::query()->where('type', 'income')->firstOrFail();
    $expenseCategory = Category::query()->where('type', 'expense')->firstOrFail();

    foreach (range(0, 34) as $day) {
        $date = now()->subDays($day)->toDateString();

        $user->transactions()->create([
            'payment_account_id' => $account->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
            'transaction_date' => $date,
            'amount' => 250000,
            'notes' => 'Income '.$day,
        ]);

        $user->transactions()->create([
            'payment_account_id' => $account->id,
            'category_id' => $expenseCategory->id,
            'type' => 'expense',
            'transaction_date' => $date,
            'amount' => 120000,
            'notes' => 'Expense '.$day,
        ]);
    }

    $this->actingAs($user)->post(route('reports.store'), [
        'title' => 'Laporan Mei',
        'period_start' => now()->subDays(29)->toDateString(),
        'period_end' => now()->toDateString(),
    ])->assertRedirect(route('reports.index'));

    $report = FinancialReport::query()->latest('id')->first();

    expect($report)->not->toBeNull()
        ->and($report->title)->toBe('Laporan Mei')
        ->and(data_get($report->payload, 'ai_analysis.analysis'))->toContain('Cashflow masih positif dan stabil.')
        ->and($report->ai_financial_insight_id)->not->toBeNull();

    Carbon::setTestNow();
});

test('user can download financial report as pdf', function () {
    $user = User::factory()->create();
    $report = FinancialReport::query()->create([
        'user_id' => $user->id,
        'title' => 'Laporan PDF',
        'period_start' => '2026-04-01',
        'period_end' => '2026-04-30',
        'payload' => [
            'summary' => [
                'income' => 1000000,
                'expense' => 750000,
                'net_cashflow' => 250000,
                'transaction_count' => 8,
                'liquid_balance_total' => 2000000,
                'upcoming_bills_total' => 300000,
                'investment_value_total' => 500000,
            ],
            'expense_by_category' => [],
            'transactions' => [],
            'ai_analysis' => [
                'analysis' => "Cashflow stabil.",
                'recommendations' => "Pertahankan disiplin.",
                'provider' => 'openrouter',
            ],
        ],
        'generated_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('reports.pdf', $report));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
