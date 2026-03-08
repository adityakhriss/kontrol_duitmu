<?php

namespace App\Services\Integrations;

use App\Models\ApiConfig;
use App\Models\ApiSyncLog;
use App\Models\InvestmentNews;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

class AlphaVantageNewsService
{
    public function config(): ApiConfig
    {
        return ApiConfig::query()->firstOrCreate(
            ['provider' => 'alpha_vantage'],
            [
                'base_url' => config('services.alpha_vantage.base_url'),
                'api_key' => config('services.alpha_vantage.key'),
                'is_active' => false,
                'default_category' => 'market_news',
                'fetch_limit' => 10,
                'sync_interval_minutes' => 60,
            ],
        );
    }

    public function sync(bool $force = false): array
    {
        $config = $this->config();
        $startedAt = now();

        $log = ApiSyncLog::query()->create([
            'provider' => 'alpha_vantage',
            'status' => 'running',
            'action' => $force ? 'manual_sync' : 'scheduled_sync',
            'message' => 'Sinkronisasi dimulai',
            'started_at' => $startedAt,
        ]);

        if (! $force && ! $config->is_active) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Integrasi Alpha Vantage belum diaktifkan.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        if (! $force && $config->last_synced_at && $config->last_synced_at->diffInMinutes(now()) < $config->sync_interval_minutes) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Belum melewati interval sync yang ditentukan.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        if (! $config->api_key) {
            $log->update([
                'status' => 'failed',
                'message' => 'API key Alpha Vantage belum tersedia.',
                'finished_at' => now(),
            ]);

            return ['status' => 'failed', 'records' => 0];
        }

        try {
            $response = $this->http()->get($config->base_url ?: config('services.alpha_vantage.base_url'), [
                'function' => 'NEWS_SENTIMENT',
                'topics' => $config->default_category ?: 'market_news',
                'limit' => $config->fetch_limit,
                'apikey' => $config->api_key,
            ])->throw()->json();

            $items = collect($response['feed'] ?? []);

            $stored = 0;

            foreach ($items as $item) {
                $externalId = Arr::get($item, 'url') ?: md5((string) Arr::get($item, 'title'));

                InvestmentNews::query()->updateOrCreate(
                    [
                        'provider' => 'alpha_vantage',
                        'external_id' => $externalId,
                    ],
                    [
                        'title' => Arr::get($item, 'title', 'Untitled'),
                        'category' => $config->default_category,
                        'source' => Arr::get($item, 'source'),
                        'url' => Arr::get($item, 'url'),
                        'summary' => Arr::get($item, 'summary'),
                        'published_at' => $this->parsePublishedAt(Arr::get($item, 'time_published')),
                        'payload' => $item,
                    ],
                );

                $stored++;
            }

            $config->update(['last_synced_at' => now()]);

            $log->update([
                'status' => 'success',
                'message' => 'Sinkronisasi Alpha Vantage berhasil.',
                'records_count' => $stored,
                'finished_at' => now(),
            ]);

            return ['status' => 'success', 'records' => $stored];
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            return ['status' => 'failed', 'records' => 0, 'message' => $exception->getMessage()];
        }
    }

    public function testConnection(): array
    {
        return $this->sync(true);
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(20);
    }

    protected function parsePublishedAt(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::createFromFormat('Ymd\THis', $value, 'UTC')->setTimezone(config('app.timezone'));
    }
}
