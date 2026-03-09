<?php

namespace App\Services\Integrations;

use App\Models\ApiConfig;
use App\Models\ApiSyncLog;
use App\Models\Investment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class YahooFinanceService
{
    public function config(): ApiConfig
    {
        return ApiConfig::query()->firstOrCreate(
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
    }

    public function syncTrackedInvestments(bool $force = false): array
    {
        $config = $this->config();
        $log = ApiSyncLog::query()->create([
            'provider' => 'yahoo_finance',
            'status' => 'running',
            'action' => $force ? 'manual_market_sync' : 'scheduled_market_sync',
            'message' => 'Sinkronisasi market data Yahoo Finance dimulai',
            'started_at' => now(),
        ]);

        if (! $config->is_active) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Integrasi Yahoo Finance belum diaktifkan.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        if (! $force && $config->last_synced_at && $config->last_synced_at->diffInMinutes(now()) < $config->sync_interval_minutes) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Belum melewati interval sync market data.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        $updated = 0;
        $failed = 0;

        Investment::query()
            ->whereNotNull('market_symbol')
            ->orderBy('id')
            ->chunk(50, function (Collection $investments) use (&$updated, &$failed): void {
                foreach ($investments as $investment) {
                    $result = $this->syncInvestment($investment, true);

                    if ($result['status'] === 'success') {
                        $updated++;
                    } else {
                        $failed++;
                    }
                }
            });

        $config->update(['last_synced_at' => now()]);

        $log->update([
            'status' => $failed > 0 && $updated === 0 ? 'failed' : 'success',
            'message' => $updated.' aset market-tracked diperbarui, '.$failed.' gagal.',
            'records_count' => $updated,
            'context' => ['failed' => $failed],
            'finished_at' => now(),
        ]);

        return ['status' => 'success', 'records' => $updated, 'failed' => $failed];
    }

    public function syncInvestment(Investment $investment, bool $useCache = true): array
    {
        $config = $this->config();

        if (! $investment->market_symbol) {
            return ['status' => 'manual', 'message' => 'Aset belum memiliki market symbol.'];
        }

        if (! $config->is_active) {
            return ['status' => 'manual', 'message' => 'Integrasi Yahoo Finance belum aktif.'];
        }

        try {
            $symbol = $this->normalizeSymbol($investment->market_symbol, $investment->market_exchange);
            $chart = $this->chart(
                $symbol,
                (string) data_get($config->settings, 'chart_interval', '1d'),
                (int) data_get($config->settings, 'chart_points', 30),
                $useCache,
            );

            $meta = $chart['meta'];
            $values = $chart['values'];
            $livePrice = (float) ($meta['regularMarketPrice'] ?? collect($values)->last()['close'] ?? $investment->current_price);
            $previousClose = (float) ($meta['chartPreviousClose'] ?? $meta['previousClose'] ?? $livePrice);
            $currentValue = (float) $investment->units * $livePrice;
            $changeAmount = $currentValue - (float) $investment->total_cost;
            $changePercent = (float) $investment->total_cost > 0
                ? ($changeAmount / (float) $investment->total_cost) * 100
                : 0;

            $investment->update([
                'ticker' => $investment->ticker ?: $symbol,
                'market_provider' => 'yahoo_finance',
                'current_price' => $livePrice,
                'current_value' => $currentValue,
                'market_change_amount' => $changeAmount,
                'market_change_percent' => $changePercent,
                'market_status' => 'live',
                'market_data_updated_at' => now(),
            ]);

            return [
                'status' => 'success',
                'quote' => [
                    'symbol' => $symbol,
                    'price' => $livePrice,
                    'previous_close' => $previousClose,
                ],
                'chart' => $chart,
                'current_price' => $livePrice,
                'current_value' => $currentValue,
                'market_change_amount' => $changeAmount,
                'market_change_percent' => $changePercent,
                'market_data_updated_at' => $investment->fresh()->market_data_updated_at,
            ];
        } catch (Throwable $exception) {
            $investment->update(['market_status' => 'error']);

            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    public function testConnection(): array
    {
        try {
            $chart = $this->chart('AAPL', '1d', 5, false);

            return [
                'status' => 'success',
                'message' => 'Koneksi Yahoo Finance berhasil.',
                'symbol' => $chart['meta']['symbol'] ?? 'AAPL',
            ];
        } catch (Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    public function chart(string $symbol, string $interval = '1d', int $points = 30, bool $useCache = true): array
    {
        $cacheKey = 'yahoo-finance.chart.'.strtolower(str_replace('.', '-', $symbol)).'.'.$interval.'.'.$points;
        $resolver = function () use ($symbol, $interval, $points): array {
            $range = $this->rangeFor($interval, $points);
            $response = $this->http()->get($this->endpoint('/v8/finance/chart/'.$symbol), [
                'interval' => $interval,
                'range' => $range,
                'includePrePost' => 'false',
                'events' => 'div,splits',
            ])->throw()->json();

            $result = data_get($response, 'chart.result.0');

            if (! $result) {
                $message = data_get($response, 'chart.error.description') ?: 'Gagal mengambil data Yahoo Finance.';
                throw new RuntimeException($message);
            }

            $timestamps = collect($result['timestamp'] ?? []);
            $quotes = data_get($result, 'indicators.quote.0', []);
            $closes = collect($quotes['close'] ?? []);

            $values = $timestamps->map(function ($timestamp, $index) use ($closes) {
                return [
                    'datetime' => now()->createFromTimestamp((int) $timestamp)->toDateTimeString(),
                    'close' => (float) ($closes[$index] ?? 0),
                ];
            })->filter(fn (array $row) => $row['close'] > 0)->values()->all();

            return [
                'meta' => $result['meta'] ?? [],
                'values' => $values,
            ];
        };

        return $useCache ? Cache::remember($cacheKey, now()->addMinutes(15), $resolver) : $resolver();
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(20);
    }

    protected function endpoint(string $path): string
    {
        return rtrim((string) ($this->config()->base_url ?: config('services.yahoo_finance.base_url')), '/').'/'.ltrim($path, '/');
    }

    protected function normalizeSymbol(string $symbol, ?string $exchange = null): string
    {
        if (str_contains($symbol, '.')) {
            return strtoupper($symbol);
        }

        return match (strtoupper((string) $exchange)) {
            'IDX', 'JK', 'JKSE' => strtoupper($symbol).'.JK',
            default => strtoupper($symbol),
        };
    }

    protected function rangeFor(string $interval, int $points): string
    {
        return match ($interval) {
            '15m' => max(1, (int) ceil($points / 26)).'d',
            '1h' => max(1, (int) ceil($points / 7)).'d',
            '1wk' => max(1, (int) ceil($points / 4)).'mo',
            default => max(1, (int) ceil($points / 22)).'mo',
        };
    }
}
