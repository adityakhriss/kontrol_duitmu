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
            'base_url' => ['required', 'url'],
            'api_key' => ['nullable', 'string', 'max:500'],
            'default_category' => ['nullable', 'string', 'max:100'],
            'fetch_limit' => ['required', 'integer', 'min:1', 'max:50'],
            'sync_interval_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'is_active' => ['nullable', 'boolean'],
            'google_calendar_enabled' => ['nullable', 'boolean'],
            'google_default_reminder_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ];
    }
}
