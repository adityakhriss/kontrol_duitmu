<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 20);
            $table->string('currency', 3)->default('IDR');
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'type']);
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('type', 20);
            $table->boolean('is_default')->default(false);
            $table->string('color', 20)->nullable();
            $table->string('icon', 50)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'slug']);
            $table->index(['type', 'is_default']);
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20);
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'transaction_date']);
        });

        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->foreignId('to_payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->date('transfer_date');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'transfer_date']);
        });

        Schema::create('account_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('source');
            $table->string('mutation_type', 30);
            $table->string('direction', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->dateTime('mutation_date');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'payment_account_id', 'mutation_date']);
        });

        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category_name');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->string('status', 20)->default('unpaid');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period', 20)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('last_generated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'due_date']);
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_on');
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('saving_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('saving_goal_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saving_goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entry_type', 20);
            $table->decimal('amount', 15, 2);
            $table->date('entry_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saving_goal_histories');
        Schema::dropIfExists('saving_goals');
        Schema::dropIfExists('bill_payments');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('account_mutations');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('payment_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
