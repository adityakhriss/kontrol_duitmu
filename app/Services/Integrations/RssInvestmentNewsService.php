<?php

namespace App\Services\Integrations;

use App\Models\ApiConfig;
use App\Models\ApiSyncLog;
use App\Models\InvestmentNews;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

class RssInvestmentNewsService
{
    public function config(): ApiConfig
    {
        return ApiConfig::query()->firstOrCreate(
            ['provider' => 'rss_news'],
            [
                'is_active' => true,
                'default_category' => 'idx_news',
                'fetch_limit' => 20,
                'sync_interval_minutes' => 60,
                'settings' => [
                    'sources' => [
                        'https://www.cnbcindonesia.com/market/rss',
                        'https://www.antaranews.com/rss/ekonomi.xml',
                    ],
                ],
            ],
        );
    }

    public function sync(bool $force = false): array
    {
        $config = $this->config();
        $log = ApiSyncLog::query()->create([
            'provider' => 'rss_news',
            'status' => 'running',
            'action' => $force ? 'manual_sync' : 'scheduled_sync',
            'message' => 'Sinkronisasi RSS dimulai',
            'started_at' => now(),
        ]);

        if (! $force && ! $config->is_active) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Integrasi RSS News belum diaktifkan.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        if (! $force && $config->last_synced_at && $config->last_synced_at->diffInMinutes(now()) < $config->sync_interval_minutes) {
            $log->update([
                'status' => 'skipped',
                'message' => 'Belum melewati interval sync RSS.',
                'finished_at' => now(),
            ]);

            return ['status' => 'skipped', 'records' => 0];
        }

        $sources = collect(data_get($config->settings, 'sources', []))->filter()->values();

        if ($sources->isEmpty()) {
            $log->update([
                'status' => 'failed',
                'message' => 'Belum ada sumber RSS yang dikonfigurasi.',
                'finished_at' => now(),
            ]);

            return ['status' => 'failed', 'records' => 0];
        }

        try {
            $stored = 0;

            foreach ($sources as $sourceUrl) {
                $xmlString = $this->http()->get($sourceUrl)->throw()->body();
                $feed = @simplexml_load_string($xmlString, SimpleXMLElement::class, LIBXML_NOCDATA);

                if (! $feed) {
                    continue;
                }

                $channel = $feed->channel ?? $feed;
                $sourceName = trim((string) ($channel->title ?? parse_url($sourceUrl, PHP_URL_HOST) ?? 'RSS Source'));
                $items = collect($feed->xpath('//item') ?: [])->take((int) $config->fetch_limit);

                foreach ($items as $item) {
                    $payload = $this->normalizeItem($item);

                    if (! $payload['title'] || ! $payload['url']) {
                        continue;
                    }

                    InvestmentNews::query()->updateOrCreate(
                        [
                            'provider' => 'rss_news',
                            'external_id' => $payload['external_id'],
                        ],
                        [
                            'title' => $payload['title'],
                            'category' => $config->default_category ?: 'idx_news',
                            'source' => $sourceName,
                            'url' => $payload['url'],
                            'image_url' => $payload['image_url'],
                            'summary' => $payload['summary'],
                            'published_at' => $payload['published_at'],
                            'payload' => $payload['payload'],
                        ],
                    );

                    $stored++;
                }
            }

            $config->update(['last_synced_at' => now()]);

            $log->update([
                'status' => 'success',
                'message' => 'Sinkronisasi RSS berita berhasil.',
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

    protected function normalizeItem(SimpleXMLElement $item): array
    {
        $namespaces = $item->getNamespaces(true);
        $media = isset($namespaces['media']) ? $item->children($namespaces['media']) : null;
        $content = isset($namespaces['content']) ? $item->children($namespaces['content']) : null;

        $title = trim(html_entity_decode((string) $item->title));
        $url = trim((string) $item->link);
        $description = trim((string) ($item->description ?? ''));
        $contentText = trim((string) ($content?->encoded ?? ''));
        $summary = $this->sanitizeSummary($contentText ?: $description);
        $imageUrl = $this->extractImageUrl($item, $description, $media);
        $publishedAt = $this->parsePublishedAt((string) ($item->pubDate ?? ''));

        return [
            'external_id' => trim((string) ($item->guid ?? '')) ?: md5($url ?: $title),
            'title' => $title,
            'url' => $url,
            'summary' => Str::limit($summary, 220),
            'image_url' => $imageUrl,
            'published_at' => $publishedAt,
            'payload' => [
                'description' => $description,
                'content' => $contentText,
            ],
        ];
    }

    protected function extractImageUrl(SimpleXMLElement $item, string $description, mixed $media): ?string
    {
        $enclosure = $item->enclosure;

        if ($enclosure && str_starts_with((string) ($enclosure['type'] ?? ''), 'image/')) {
            return (string) $enclosure['url'];
        }

        if ($media && isset($media->content)) {
            return (string) $media->content->attributes()->url;
        }

        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function sanitizeSummary(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags(html_entity_decode($value))));
    }

    protected function parsePublishedAt(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }

    protected function http(): PendingRequest
    {
        return Http::accept('application/rss+xml, application/xml, text/xml')->timeout(20);
    }
}
