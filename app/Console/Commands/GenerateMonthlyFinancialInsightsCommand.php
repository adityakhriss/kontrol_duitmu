<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Finance\FinancialAnalysisSnapshotService;
use Illuminate\Console\Command;

class GenerateMonthlyFinancialInsightsCommand extends Command
{
    protected $signature = 'finance:generate-monthly-ai-insights';

    protected $description = 'Generate monthly AI insight snapshots for users with transactions';

    public function handle(FinancialAnalysisSnapshotService $snapshotService): int
    {
        $generated = 0;
        $existing = 0;
        $skipped = 0;
        $failed = 0;

        User::query()
            ->whereHas('transactions')
            ->chunk(100, function ($users) use ($snapshotService, &$generated, &$existing, &$skipped, &$failed): void {
                foreach ($users as $user) {
                    $result = $snapshotService->getOrCreateMonthlyInsight($user);

                    match ($result['status'] ?? null) {
                        'generated' => $generated++,
                        'existing' => $existing++,
                        'ineligible', 'unavailable' => $skipped++,
                        default => $failed++,
                    };
                }
            });

        $this->info('Generated: '.$generated);
        $this->line('Existing: '.$existing);
        $this->line('Skipped: '.$skipped);
        $this->line('Failed: '.$failed);

        return self::SUCCESS;
    }
}
