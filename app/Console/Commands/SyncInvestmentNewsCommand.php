<?php

namespace App\Console\Commands;

use App\Services\Integrations\RssInvestmentNewsService;
use Illuminate\Console\Command;

class SyncInvestmentNewsCommand extends Command
{
    protected $signature = 'finance:sync-investment-news {--force : Run sync regardless of schedule window}';

    protected $description = 'Synchronize investment news from RSS sources';

    public function handle(RssInvestmentNewsService $service): int
    {
        $result = $service->sync((bool) $this->option('force'));
        $this->info('Investment news sync: '.($result['status'] ?? 'unknown'));

        return self::SUCCESS;
    }
}
