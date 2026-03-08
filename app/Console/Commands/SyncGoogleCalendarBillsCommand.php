<?php

namespace App\Console\Commands;

use App\Services\Integrations\GoogleCalendarSyncService;
use Illuminate\Console\Command;

class SyncGoogleCalendarBillsCommand extends Command
{
    protected $signature = 'finance:sync-google-calendar-bills';

    protected $description = 'Run Google Calendar bill sync placeholder';

    public function handle(GoogleCalendarSyncService $service): int
    {
        $result = $service->syncUpcomingBills();
        $this->info($result['message'] ?? 'Google Calendar sync completed.');

        return self::SUCCESS;
    }
}
