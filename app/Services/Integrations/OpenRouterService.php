<?php

namespace App\Services\Integrations;

use App\Models\ApiConfig;
use App\Services\Integrations\Contracts\AiAnalysisProviderInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService implements AiAnalysisProviderInterface
{
    public function providerKey(): string
    {
        return 'openrouter';
    }

    public function providerLabel(): string
    {
        return 'OpenRouter';
    }

    public function config(): ApiConfig
    {
        return ApiConfig::query()->firstOrCreate(
            ['provider' => $this->providerKey()],
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
    }

    public function isReady(): bool
    {
        $config = $this->config();

        return $config->is_active && filled($config->api_key);
    }

    public function analyzeFinancialSummary(array $summary): array
    {
        $config = $this->config();

        if (! $config->is_active) {
            throw new RuntimeException('Integrasi OpenRouter belum diaktifkan.');
        }

        if (! $config->api_key) {
            throw new RuntimeException('API key OpenRouter belum tersedia.');
        }

        $response = $this->http($config->api_key)->post($this->endpoint($config), [
            'model' => data_get($config->settings, 'model', config('services.openrouter.model', 'openrouter/auto')),
            'response_format' => [
                'type' => 'json_object',
            ],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah analis keuangan pribadi berbahasa Indonesia. Gunakan hanya data yang diberikan. Berikan analisis yang ringkas, spesifik, dan action-oriented. Balas JSON valid dengan key: analysis, recommendations, summary_title. Nilai analysis dan recommendations masing-masing berupa array string 3-6 item.',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ],
        ])->throw()->json();

        $content = Arr::get($response, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('OpenRouter tidak mengembalikan konten analisis.');
        }

        $parsed = json_decode($content, true);

        if (! is_array($parsed)) {
            throw new RuntimeException('Respons OpenRouter tidak valid sebagai JSON.');
        }

        return $parsed;
    }

    public function testConnection(): array
    {
        try {
            $result = $this->analyzeFinancialSummary([
                'meta' => ['test' => true],
                'summary' => ['income' => 10000000, 'expense' => 7500000],
            ]);

            return [
                'status' => 'success',
                'message' => 'Koneksi OpenRouter berhasil.',
                'title' => $result['summary_title'] ?? 'OK',
            ];
        } catch (\Throwable $exception) {
            return ['status' => 'failed', 'message' => $exception->getMessage()];
        }
    }

    protected function http(string $apiKey): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(45)
            ->withToken($apiKey)
            ->withHeaders([
                'HTTP-Referer' => config('services.openrouter.site_url'),
                'X-OpenRouter-Title' => config('services.openrouter.app_name'),
            ]);
    }

    protected function endpoint(ApiConfig $config): string
    {
        return rtrim((string) ($config->base_url ?: config('services.openrouter.base_url')), '/').'/chat/completions';
    }
}
