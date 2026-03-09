<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IntegrationSettingsController;
use App\Http\Controllers\Admin\SyncLogController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavingGoalController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/accounts', [PaymentAccountController::class, 'index'])->name('accounts.index');

    Route::get('/accounts/transfer', [TransferController::class, 'create'])->name('accounts.transfer');
    Route::post('/accounts/transfer', [TransferController::class, 'store'])->name('transfers.store');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');

    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::post('/bills/{bill}/payments', [BillController::class, 'storePayment'])->name('bills.payments.store');
    Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');

    Route::get('/saving-goals', [SavingGoalController::class, 'index'])->name('saving-goals.index');
    Route::post('/saving-goals', [SavingGoalController::class, 'store'])->name('saving-goals.store');
    Route::post('/saving-goals/{savingGoal}/entries', [SavingGoalController::class, 'storeEntry'])->name('saving-goals.entries.store');
    Route::delete('/saving-goals/{savingGoal}', [SavingGoalController::class, 'destroy'])->name('saving-goals.destroy');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::post('/debts', [DebtController::class, 'store'])->name('debts.store');
    Route::post('/debts/{debt}/payments', [DebtController::class, 'storePayment'])->name('debts.payments.store');
    Route::delete('/debts/{debt}', [DebtController::class, 'destroy'])->name('debts.destroy');

    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
    Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
    Route::post('/investments/{investment}/transactions', [InvestmentController::class, 'storeTransaction'])->name('investments.transactions.store');
    Route::delete('/investments/{investment}', [InvestmentController::class, 'destroy'])->name('investments.destroy');
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/analysis', [AnalysisController::class, 'index'])->name('analysis.index');
    Route::get('/reports', [FinancialReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [FinancialReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [FinancialReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/pdf', [FinancialReportController::class, 'downloadPdf'])->name('reports.pdf');

    Route::prefix('admin')->name('admin.')->middleware('can:access-admin')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/api-settings', [IntegrationSettingsController::class, 'edit'])->name('api-settings');
        Route::put('/api-settings', [IntegrationSettingsController::class, 'update'])->name('api-settings.update');
        Route::post('/api-settings/sync', [IntegrationSettingsController::class, 'syncNow'])->name('api-settings.sync');
        Route::post('/api-settings/test-yahoo-finance', [IntegrationSettingsController::class, 'testYahooFinance'])->name('api-settings.test-yahoo-finance');
        Route::post('/api-settings/test-ai-provider', [IntegrationSettingsController::class, 'testAiProvider'])->name('api-settings.test-ai-provider');
        Route::get('/sync-logs', [SyncLogController::class, 'index'])->name('sync-logs');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/reset-data', [ProfileController::class, 'resetData'])->name('profile.reset-data');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
