<x-app-layout>
    <x-slot name="title">API Berita</x-slot>
    <x-slot name="heading">Setting API Berita Investasi</x-slot>
    <x-slot name="description">Frontend awal untuk konfigurasi Alpha Vantage, test connection, cache, dan interval sync.</x-slot>
    <x-slot name="navigation">admin</x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <x-ui.panel title="Konfigurasi API" description="Nanti nilainya diambil dari settings atau api_configs.">
            <form method="POST" action="{{ route('admin.api-settings.update') }}" class="grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <x-input-label for="base-url" value="Base URL" />
                    <x-text-input id="base-url" name="base_url" type="text" :value="old('base_url', $apiConfig?->base_url ?? config('services.alpha_vantage.base_url'))" />
                </div>
                <div>
                    <x-input-label for="api-key" value="API Key" />
                    <x-text-input id="api-key" name="api_key" type="password" value="" placeholder="Biarkan kosong untuk tetap pakai nilai yang tersimpan" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="category" value="Kategori default" />
                        <select id="category" name="default_category" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"><option value="market_news" @selected(($apiConfig?->default_category ?? 'market_news') === 'market_news')>market_news</option><option value="economy_fiscal" @selected(($apiConfig?->default_category ?? '') === 'economy_fiscal')>economy_fiscal</option></select>
                    </div>
                    <div>
                        <x-input-label for="interval" value="Interval sync" />
                        <select id="interval" name="sync_interval_minutes" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"><option value="30" @selected(($apiConfig?->sync_interval_minutes ?? 60) == 30)>30 menit</option><option value="60" @selected(($apiConfig?->sync_interval_minutes ?? 60) == 60)>60 menit</option><option value="180" @selected(($apiConfig?->sync_interval_minutes ?? 60) == 180)>180 menit</option></select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="fetch-limit" value="Jumlah data" />
                        <x-text-input id="fetch-limit" name="fetch_limit" type="number" :value="old('fetch_limit', $apiConfig?->fetch_limit ?? 10)" />
                    </div>
                    <div>
                        <x-input-label for="google-reminder" value="Reminder Google Calendar (menit)" />
                        <x-text-input id="google-reminder" name="google_default_reminder_minutes" type="number" :value="old('google_default_reminder_minutes', data_get($googleReminderSettings?->value, 'minutes', 60))" />
                    </div>
                </div>
                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked($apiConfig?->is_active)> Aktifkan Alpha Vantage</label>
                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="google_calendar_enabled" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(data_get($googleCalendarSettings?->value, 'enabled', false))> Aktifkan setting Google Calendar</label>
                <div class="flex gap-3">
                    <x-primary-button>Simpan setting</x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.api-settings.sync') }}" class="mt-3">
                @csrf
                <x-secondary-button type="submit">Test connection</x-secondary-button>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Status API" description="Informasi cepat untuk admin.">
            <div class="space-y-3 text-sm">
                <div class="rounded-[1.5rem] {{ $apiConfig?->is_active ? 'bg-emerald-50 text-emerald-900' : 'bg-amber-50 text-amber-900' }} p-4">{{ $apiConfig?->is_active ? 'Integrasi Alpha Vantage aktif.' : 'Integrasi Alpha Vantage belum aktif.' }}</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Cache berita aktif sehingga user tetap melihat artikel terbaru saat request baru gagal. Sync terakhir: {{ $apiConfig?->last_synced_at ? \App\Support\FinancePresenter::shortDate($apiConfig->last_synced_at) : 'belum pernah' }}.</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Google client id dari env: {{ config('services.google.client_id') ? 'terdeteksi' : 'belum diisi' }}.</div>
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
