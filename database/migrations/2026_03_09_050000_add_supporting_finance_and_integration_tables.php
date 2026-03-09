<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('lender_name')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('remaining_amount', 15, 2);
            $table->decimal('monthly_payment', 15, 2)->default(0);
            $table->decimal('interest_rate', 8, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_on');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('ticker')->nullable();
            $table->string('type', 30);
            $table->decimal('units', 18, 8)->default(0);
            $table->decimal('buy_price', 15, 2)->default(0);
            $table->decimal('current_price', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('platform')->nullable();
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->date('transaction_date');
            $table->decimal('units', 18, 8);
            $table->decimal('price', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('api_configs', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->string('base_url')->nullable();
            $table->text('api_key')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('default_category')->nullable();
            $table->unsignedInteger('fetch_limit')->default(10);
            $table->unsignedInteger('sync_interval_minutes')->default(60);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('investment_news', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('rss_news');
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('source')->nullable();
            $table->string('url', 2048)->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'published_at']);
        });

        Schema::create('api_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('status', 20);
            $table->string('action');
            $table->text('message')->nullable();
            $table->unsignedInteger('records_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['provider', 'created_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('google_calendar_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('google_email')->nullable();
            $table->string('calendar_id')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_calendar_connections');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('api_sync_logs');
        Schema::dropIfExists('investment_news');
        Schema::dropIfExists('api_configs');
        Schema::dropIfExists('investment_transactions');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
