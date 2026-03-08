<?php

namespace Database\Seeders;

use App\Models\ApiConfig;
use App\Models\ApiSyncLog;
use App\Models\InvestmentNews;
use App\Models\Setting;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    public function run(): void
    {
        ApiConfig::query()->updateOrCreate(
            ['provider' => 'alpha_vantage'],
            [
                'base_url' => config('services.alpha_vantage.base_url'),
                'api_key' => config('services.alpha_vantage.key'),
                'is_active' => true,
                'default_category' => 'market_news',
                'fetch_limit' => 10,
                'sync_interval_minutes' => 60,
                'last_synced_at' => now()->subMinutes(40),
            ],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.enabled'],
            ['group' => 'google_calendar', 'value' => ['enabled' => true]],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.default_reminder_minutes'],
            ['group' => 'google_calendar', 'value' => ['minutes' => 60]],
        );

        $baseTime = CarbonImmutable::now();

        foreach ([
            ['title' => 'Pasar saham Asia menguat setelah inflasi melandai', 'category' => 'Makro'],
            ['title' => 'Emas bertahan tinggi di tengah minat aset aman', 'category' => 'Komoditas'],
            ['title' => 'Investor retail rotasi ke aset defensif menjelang kuartal baru', 'category' => 'Sentimen'],
        ] as $index => $article) {
            InvestmentNews::query()->updateOrCreate(
                ['provider' => 'alpha_vantage', 'external_id' => 'seeded-'.$index],
                [
                    'title' => $article['title'],
                    'category' => $article['category'],
                    'source' => 'Alpha Vantage (seed)',
                    'url' => 'https://www.alphavantage.co/',
                    'summary' => 'Artikel seed untuk fallback cache dashboard dan halaman berita.',
                    'published_at' => $baseTime->subHours($index + 1),
                    'payload' => ['seeded' => true],
                ],
            );
        }

        foreach ([
            ['provider' => 'alpha_vantage', 'status' => 'success', 'action' => 'scheduled_sync', 'message' => '25 artikel terbaru disimpan ke cache.', 'records_count' => 25],
            ['provider' => 'google_calendar', 'status' => 'skipped', 'action' => 'sync_bill_events', 'message' => 'Belum ada user yang menghubungkan Google Calendar.', 'records_count' => 0],
        ] as $index => $log) {
            ApiSyncLog::query()->updateOrCreate(
                [
                    'provider' => $log['provider'],
                    'action' => $log['action'],
                    'message' => $log['message'],
                ],
                [
                    'status' => $log['status'],
                    'records_count' => $log['records_count'],
                    'started_at' => $baseTime->subMinutes(($index + 1) * 45),
                    'finished_at' => $baseTime->subMinutes(($index + 1) * 45 - 2),
                ],
            );
        }
    }
}
