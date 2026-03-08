<?php

namespace App\Services\Integrations;

use App\Models\ApiSyncLog;
use App\Models\GoogleCalendarConnection;
use App\Models\Setting;

class GoogleCalendarSyncService
{
    public function syncUpcomingBills(): array
    {
        $enabled = (bool) data_get(Setting::query()->where('key', 'google_calendar.enabled')->first()?->value, 'enabled', false);

        $connections = GoogleCalendarConnection::query()->where('is_active', true)->count();

        $status = $enabled && $connections > 0 ? 'success' : 'skipped';
        $message = ! $enabled
            ? 'Google Calendar integration belum diaktifkan.'
            : ($connections === 0 ? 'Belum ada user yang menghubungkan Google Calendar.' : 'Sinkronisasi placeholder berhasil dijalankan.');

        ApiSyncLog::query()->create([
            'provider' => 'google_calendar',
            'status' => $status,
            'action' => 'sync_bill_events',
            'message' => $message,
            'records_count' => 0,
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        return ['status' => $status, 'message' => $message];
    }
}
