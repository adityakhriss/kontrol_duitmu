<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationSettingsRequest;
use App\Models\Setting;
use App\Services\Integrations\AiProviderManager;
use App\Services\Integrations\OpenRouterService;
use App\Services\Integrations\RssInvestmentNewsService;
use App\Services\Integrations\YahooFinanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class IntegrationSettingsController extends Controller
{
    public function __construct(
        private readonly RssInvestmentNewsService $rssInvestmentNewsService,
        private readonly YahooFinanceService $yahooFinanceService,
        private readonly OpenRouterService $openRouterService,
        private readonly AiProviderManager $aiProviderManager,
    )
    {
    }

    public function edit(): View
    {
        return view('admin.api-settings', [
            'rssNewsConfig' => $this->rssInvestmentNewsService->config(),
            'yahooFinanceConfig' => $this->yahooFinanceService->config(),
            'openrouterConfig' => $this->openRouterService->config(),
            'aiProviderOptions' => $this->aiProviderManager->options(),
            'currentAiProvider' => $this->aiProviderManager->currentProviderKey(),
            'googleCalendarSettings' => Setting::query()->where('key', 'google_calendar.enabled')->first(),
            'googleReminderSettings' => Setting::query()->where('key', 'google_calendar.default_reminder_minutes')->first(),
        ]);
    }

    public function update(UpdateIntegrationSettingsRequest $request): RedirectResponse
    {
        $rssNewsConfig = $this->rssInvestmentNewsService->config();
        $yahooFinanceConfig = $this->yahooFinanceService->config();
        $openrouterConfig = $this->openRouterService->config();
        $data = $request->validated();

        $rssSources = collect(preg_split('/\r\n|\r|\n/', $data['rss_sources']))
            ->map(fn ($source) => trim((string) $source))
            ->filter()
            ->values()
            ->all();

        $rssNewsConfig->update([
            'default_category' => $data['rss_default_category'] ?: 'idx_news',
            'fetch_limit' => $data['rss_fetch_limit'],
            'sync_interval_minutes' => $data['rss_sync_interval_minutes'],
            'is_active' => (bool) ($data['rss_enabled'] ?? false),
            'settings' => array_merge($rssNewsConfig->settings ?? [], [
                'sources' => $rssSources,
            ]),
        ]);

        $yahooFinanceConfig->update([
            'base_url' => $data['yahoo_finance_base_url'],
            'fetch_limit' => max((int) $yahooFinanceConfig->fetch_limit, (int) $data['yahoo_finance_chart_points']),
            'sync_interval_minutes' => $data['yahoo_finance_sync_interval_minutes'],
            'is_active' => (bool) ($data['yahoo_finance_enabled'] ?? false),
            'settings' => array_merge($yahooFinanceConfig->settings ?? [], [
                'chart_interval' => $data['yahoo_finance_chart_interval'],
                'chart_points' => (int) $data['yahoo_finance_chart_points'],
            ]),
        ]);

        $openrouterConfig->update([
            'base_url' => $data['openrouter_base_url'],
            'api_key' => $data['openrouter_api_key'] ?: $openrouterConfig->api_key ?: config('services.openrouter.key'),
            'is_active' => (bool) ($data['openrouter_enabled'] ?? false),
            'settings' => array_merge($openrouterConfig->settings ?? [], [
                'model' => $data['openrouter_model'],
            ]),
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'ai.provider'],
            ['group' => 'ai', 'value' => ['provider' => $data['ai_default_provider']]],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.enabled'],
            ['group' => 'google_calendar', 'value' => ['enabled' => (bool) ($data['google_calendar_enabled'] ?? false)]],
        );

        Setting::query()->updateOrCreate(
            ['key' => 'google_calendar.default_reminder_minutes'],
            ['group' => 'google_calendar', 'value' => ['minutes' => (int) $data['google_default_reminder_minutes']]],
        );

        return back()->with('status', 'Pengaturan integrasi berhasil disimpan.');
    }

    public function syncNow(): RedirectResponse
    {
        $newsResult = $this->rssInvestmentNewsService->testConnection();
        $marketResult = $this->yahooFinanceService->syncTrackedInvestments(true);
        $aiResult = $this->aiProviderManager->testCurrentProvider();

        return back()->with('status', 'RSS News: '.($newsResult['message'] ?? $newsResult['status']).'. Yahoo Finance: '.($marketResult['message'] ?? $marketResult['status']).'. AI Provider: '.($aiResult['message'] ?? $aiResult['status']).'.');
    }

    public function testYahooFinance(): RedirectResponse
    {
        $result = $this->yahooFinanceService->testConnection();

        return back()->with('status', 'Yahoo Finance: '.($result['message'] ?? $result['status']).'.');
    }

    public function testAiProvider(): RedirectResponse
    {
        $result = $this->aiProviderManager->testCurrentProvider();

        return back()->with('status', 'AI Provider: '.($result['message'] ?? $result['status']).'.');
    }
}
