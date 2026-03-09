<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_news', function (Blueprint $table) {
            $table->string('image_url', 2048)->nullable()->after('url');
        });

        $hasRssConfig = DB::table('api_configs')->where('provider', 'rss_news')->exists();

        if ($hasRssConfig) {
            DB::table('api_configs')->where('provider', 'alpha_vantage')->delete();
        } else {
            DB::table('api_configs')->where('provider', 'alpha_vantage')->update([
                'provider' => 'rss_news',
                'base_url' => null,
                'api_key' => null,
                'default_category' => 'idx_news',
                'settings' => json_encode([
                    'sources' => [
                        'https://www.cnbcindonesia.com/market/rss',
                        'https://www.antaranews.com/rss/ekonomi.xml',
                    ],
                ]),
            ]);
        }

        DB::table('api_sync_logs')->where('provider', 'alpha_vantage')->update(['provider' => 'rss_news']);
        DB::table('investment_news')->where('provider', 'alpha_vantage')->update(['provider' => 'rss_news']);
    }

    public function down(): void
    {
        Schema::table('investment_news', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });

        $hasAlphaConfig = DB::table('api_configs')->where('provider', 'alpha_vantage')->exists();

        if ($hasAlphaConfig) {
            DB::table('api_configs')->where('provider', 'rss_news')->delete();
        } else {
            DB::table('api_configs')->where('provider', 'rss_news')->update([
                'provider' => 'alpha_vantage',
                'default_category' => 'market_news',
            ]);
        }

        DB::table('api_sync_logs')->where('provider', 'rss_news')->update(['provider' => 'alpha_vantage']);
        DB::table('investment_news')->where('provider', 'rss_news')->update(['provider' => 'alpha_vantage']);
    }
};
