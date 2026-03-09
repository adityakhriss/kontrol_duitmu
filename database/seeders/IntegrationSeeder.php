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
            ['provider' => 'rss_news'],
            [
                'is_active' => true,
                'default_category' => 'idx_news',
                'fetch_limit' => 20,
                'sync_interval_minutes' => 60,
                'last_synced_at' => now()->subMinutes(40),
                'settings' => [
                    'sources' => config('services.rss_news.sources'),
                ],
            ],
        );

        ApiConfig::query()->updateOrCreate(
            ['provider' => 'yahoo_finance'],
            [
                'base_url' => config('services.yahoo_finance.base_url'),
                'is_active' => false,
                'fetch_limit' => 20,
                'sync_interval_minutes' => 15,
                'settings' => [
                    'chart_interval' => '1d',
                    'chart_points' => 30,
                ],
            ],
        );

        ApiConfig::query()->updateOrCreate(
            ['provider' => 'openrouter'],
            [
                'base_url' => config('services.openrouter.base_url'),
                'api_key' => config('services.openrouter.key'),
                'is_active' => false,
                'fetch_limit' => 1,
                'sync_interval_minutes' => 43200,
                'settings' => [
                    'model' => config('services.openrouter.model', 'openrouter/auto'),
                ],
            ],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.enabled'],
            ['group' => 'google_calendar', 'value' => ['enabled' => true]],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'ai.provider'],
            ['group' => 'ai', 'value' => ['provider' => 'openrouter']],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.default_reminder_minutes'],
            ['group' => 'google_calendar', 'value' => ['minutes' => 60]],
        );

        $baseTime = CarbonImmutable::now();

        foreach ([
            ['title' => 'IHSG bergerak volatil menjelang penutupan perdagangan', 'category' => 'IDX', 'source' => 'CNBC Indonesia (seed)'],
            ['title' => 'Saham perbankan jadi sorotan investor asing pekan ini', 'category' => 'IDX', 'source' => 'ANTARA (seed)'],
            ['title' => 'Emiten energi menguat setelah harga komoditas naik', 'category' => 'IDX', 'source' => 'CNBC Indonesia (seed)'],
        ] as $index => $article) {
            InvestmentNews::query()->updateOrCreate(
                ['provider' => 'rss_news', 'external_id' => 'seeded-'.$index],
                [
                    'title' => $article['title'],
                    'category' => $article['category'],
                    'source' => $article['source'],
                    'url' => 'https://www.cnbcindonesia.com/market',
                    'image_url' => 'https://cdn.cnbcindonesia.com/cnbc/images/logo/2026/cnbc-logo.png',
                    'summary' => 'Artikel seed untuk fallback cache dashboard dan halaman berita RSS.',
                    'published_at' => $baseTime->subHours($index + 1),
                    'payload' => ['seeded' => true],
                ],
            );
        }

        foreach ([
            ['provider' => 'rss_news', 'status' => 'success', 'action' => 'scheduled_sync', 'message' => 'Feed RSS IDX berhasil disimpan ke cache.', 'records_count' => 25],
            ['provider' => 'yahoo_finance', 'status' => 'skipped', 'action' => 'scheduled_market_sync', 'message' => 'Belum ada market symbol yang aktif untuk disinkronkan.', 'records_count' => 0],
            ['provider' => 'openrouter', 'status' => 'skipped', 'action' => 'generate_financial_insight', 'message' => 'Belum ada insight AI bulanan yang digenerate.', 'records_count' => 0],
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
