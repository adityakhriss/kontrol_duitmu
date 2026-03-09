<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'rss_default_category' => ['nullable', 'string', 'max:100'],
            'rss_fetch_limit' => ['required', 'integer', 'min:1', 'max:50'],
            'rss_sync_interval_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'rss_sources' => ['required', 'string', 'max:5000'],
            'rss_enabled' => ['nullable', 'boolean'],
            'yahoo_finance_base_url' => ['required', 'url'],
            'yahoo_finance_sync_interval_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'yahoo_finance_chart_interval' => ['required', 'string', 'max:30'],
            'yahoo_finance_chart_points' => ['required', 'integer', 'min:5', 'max:100'],
            'yahoo_finance_enabled' => ['nullable', 'boolean'],
            'ai_default_provider' => ['required', 'string', 'max:50'],
            'openrouter_base_url' => ['required', 'url'],
            'openrouter_api_key' => ['nullable', 'string', 'max:500'],
            'openrouter_model' => ['required', 'string', 'max:100'],
            'openrouter_enabled' => ['nullable', 'boolean'],
            'google_calendar_enabled' => ['nullable', 'boolean'],
            'google_default_reminder_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ];
    }
}
