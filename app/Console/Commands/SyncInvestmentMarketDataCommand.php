<?php

namespace App\Console\Commands;

use App\Services\Integrations\YahooFinanceService;
use Illuminate\Console\Command;

class SyncInvestmentMarketDataCommand extends Command
{
    protected $signature = 'finance:sync-investment-market-data {--force : Abaikan interval sync}';

    protected $description = 'Sinkronisasi market data investasi dari Yahoo Finance';

    public function handle(YahooFinanceService $yahooFinanceService): int
    {
        $result = $yahooFinanceService->syncTrackedInvestments((bool) $this->option('force'));

        $this->info('Status: '.($result['status'] ?? 'unknown'));
        $this->info('Records: '.($result['records'] ?? 0));

        if (isset($result['failed'])) {
            $this->line('Failed: '.$result['failed']);
        }

        return self::SUCCESS;
    }
}
