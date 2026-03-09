<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('investment_news')
            ->where('source', 'like', '%Alpha Vantage%')
            ->update(['source' => 'RSS News IDX']);
    }

    public function down(): void
    {
        DB::table('investment_news')
            ->where('source', 'RSS News IDX')
            ->where('provider', 'rss_news')
            ->update(['source' => 'Alpha Vantage (seed)']);
    }
};
