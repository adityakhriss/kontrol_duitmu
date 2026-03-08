<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationSettingsRequest;
use App\Models\ApiConfig;
use App\Models\Setting;
use App\Services\Integrations\AlphaVantageNewsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class IntegrationSettingsController extends Controller
{
    public function __construct(private readonly AlphaVantageNewsService $alphaVantageNewsService)
    {
    }

    public function edit(): View
    {
        return view('admin.api-settings', [
            'apiConfig' => $this->alphaVantageNewsService->config(),
            'googleCalendarSettings' => Setting::query()->where('key', 'google_calendar.enabled')->first(),
            'googleReminderSettings' => Setting::query()->where('key', 'google_calendar.default_reminder_minutes')->first(),
        ]);
    }

    public function update(UpdateIntegrationSettingsRequest $request): RedirectResponse
    {
        $config = $this->alphaVantageNewsService->config();
        $data = $request->validated();

        $config->update([
            'base_url' => $data['base_url'],
            'api_key' => $data['api_key'] ?: $config->api_key ?: config('services.alpha_vantage.key'),
            'default_category' => $data['default_category'] ?: 'market_news',
            'fetch_limit' => $data['fetch_limit'],
            'sync_interval_minutes' => $data['sync_interval_minutes'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

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
        $result = $this->alphaVantageNewsService->testConnection();

        return back()->with('status', 'Sync Alpha Vantage: '.($result['message'] ?? $result['status']).'.');
    }
}
