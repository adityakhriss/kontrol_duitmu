<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasYahooConfig = DB::table('api_configs')->where('provider', 'yahoo_finance')->exists();

        if ($hasYahooConfig) {
            DB::table('api_configs')->where('provider', 'twelve_data')->delete();
        } else {
            DB::table('api_configs')
                ->where('provider', 'twelve_data')
                ->update(['provider' => 'yahoo_finance']);
        }

        DB::table('api_sync_logs')
            ->where('provider', 'twelve_data')
            ->update(['provider' => 'yahoo_finance']);

        DB::table('investments')
            ->where('market_provider', 'twelve_data')
            ->update(['market_provider' => 'yahoo_finance']);
    }

    public function down(): void
    {
        DB::table('api_configs')
            ->where('provider', 'yahoo_finance')
            ->update(['provider' => 'twelve_data']);

        DB::table('api_sync_logs')
            ->where('provider', 'yahoo_finance')
            ->update(['provider' => 'twelve_data']);

        DB::table('investments')
            ->where('market_provider', 'yahoo_finance')
            ->update(['market_provider' => 'twelve_data']);
    }
};
