<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->string('market_symbol')->nullable()->after('ticker');
            $table->string('market_exchange')->nullable()->after('market_symbol');
            $table->string('market_provider', 50)->nullable()->after('market_exchange');
            $table->decimal('market_change_percent', 10, 4)->nullable()->after('current_value');
            $table->decimal('market_change_amount', 15, 2)->nullable()->after('market_change_percent');
            $table->timestamp('market_data_updated_at')->nullable()->after('market_change_amount');
            $table->string('market_status', 30)->default('manual')->after('market_data_updated_at');

            $table->index(['market_provider', 'market_symbol']);
            $table->index(['user_id', 'market_status']);
        });
    }

    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropIndex(['market_provider', 'market_symbol']);
            $table->dropIndex(['user_id', 'market_status']);
            $table->dropColumn([
                'market_symbol',
                'market_exchange',
                'market_provider',
                'market_change_percent',
                'market_change_amount',
                'market_data_updated_at',
                'market_status',
            ]);
        });
    }
};
