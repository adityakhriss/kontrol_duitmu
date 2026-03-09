@php
    $yahooChartInterval = data_get($yahooFinanceConfig?->settings, 'chart_interval', '1d');
    $yahooChartPoints = data_get($yahooFinanceConfig?->settings, 'chart_points', 30);
    $rssSources = implode("\n", data_get($rssNewsConfig?->settings, 'sources', config('services.rss_news.sources', [])));
    $openrouterModel = data_get($openrouterConfig?->settings, 'model', config('services.openrouter.model', 'openrouter/auto'));
    $selectedAiProvider = old('ai_default_provider', $currentAiProvider ?? 'openrouter');
@endphp

<x-app-layout>
    <x-slot name="title">Integrasi API</x-slot>
    <x-slot name="heading">Setting Integrasi Investasi</x-slot>
    <x-slot name="description">Kelola RSS berita IDX, Yahoo Finance, AI provider, dan Google Calendar dari satu halaman admin.</x-slot>
    <x-slot name="navigation">admin</x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <x-ui.panel title="Konfigurasi API" description="Simpan sumber berita dan pengaturan market data dari panel ini.">
            <form method="POST" action="{{ route('admin.api-settings.update') }}" class="grid gap-4">
                @csrf
                @method('PUT')

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-950">RSS News IDX</p>
                    <p class="mt-1 text-sm text-slate-500">Agregasi berita pasar modal Indonesia dari sumber RSS publik seperti CNBC Indonesia dan ANTARA.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="rss-default-category" value="Kategori default" />
                        <x-text-input id="rss-default-category" name="rss_default_category" type="text" :value="old('rss_default_category', $rssNewsConfig?->default_category ?? 'idx_news')" />
                    </div>
                    <div>
                        <x-input-label for="rss-fetch-limit" value="Jumlah item per sumber" />
                        <x-text-input id="rss-fetch-limit" name="rss_fetch_limit" type="number" :value="old('rss_fetch_limit', $rssNewsConfig?->fetch_limit ?? 20)" />
                    </div>
                </div>
                <div>
                    <x-input-label for="rss-sync-interval" value="Interval sync RSS (menit)" />
                    <x-text-input id="rss-sync-interval" name="rss_sync_interval_minutes" type="number" :value="old('rss_sync_interval_minutes', $rssNewsConfig?->sync_interval_minutes ?? 60)" />
                </div>
                <div>
                    <x-input-label for="rss-sources" value="Daftar sumber RSS" />
                    <textarea id="rss-sources" name="rss_sources" rows="6" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('rss_sources', $rssSources) }}</textarea>
                </div>
                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="rss_enabled" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked($rssNewsConfig?->is_active)> Aktifkan RSS News</label>

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-950">Yahoo Finance</p>
                    <p class="mt-1 text-sm text-slate-500">Dipakai untuk chart dan valuasi market pada aset investasi yang punya market symbol. Integrasi ini tidak memerlukan API key atau token.</p>
                </div>
                <div>
                    <x-input-label for="yahoo-finance-base-url" value="Base URL Yahoo Finance" />
                    <x-text-input id="yahoo-finance-base-url" name="yahoo_finance_base_url" type="text" :value="old('yahoo_finance_base_url', $yahooFinanceConfig?->base_url ?? config('services.yahoo_finance.base_url'))" />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="yahoo-finance-sync-interval" value="Interval sync market" />
                        <x-text-input id="yahoo-finance-sync-interval" name="yahoo_finance_sync_interval_minutes" type="number" :value="old('yahoo_finance_sync_interval_minutes', $yahooFinanceConfig?->sync_interval_minutes ?? 15)" />
                    </div>
                    <div>
                        <x-input-label for="yahoo-finance-chart-interval" value="Interval chart" />
                        <select id="yahoo-finance-chart-interval" name="yahoo_finance_chart_interval" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach (['15m', '1h', '1d', '1wk'] as $interval)
                                <option value="{{ $interval }}" @selected(old('yahoo_finance_chart_interval', $yahooChartInterval) === $interval)>{{ $interval }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="yahoo-finance-chart-points" value="Jumlah titik chart" />
                        <x-text-input id="yahoo-finance-chart-points" name="yahoo_finance_chart_points" type="number" :value="old('yahoo_finance_chart_points', $yahooChartPoints)" />
                    </div>
                </div>
                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="yahoo_finance_enabled" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked($yahooFinanceConfig?->is_active)> Aktifkan Yahoo Finance</label>

                <div class="rounded-[1.5rem] border border-slate-200 bg-slate-50 p-4">
                    <p class="font-semibold text-slate-950">AI Provider</p>
                    <p class="mt-1 text-sm text-slate-500">Digunakan untuk membuat snapshot analisis dan saran AI bulanan. Arsitektur ini disiapkan agar bisa mendukung banyak provider AI, bukan hanya OpenRouter.</p>
                </div>
                <div>
                    <x-input-label for="ai-default-provider" value="Provider AI aktif" />
                    <select id="ai-default-provider" name="ai_default_provider" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach ($aiProviderOptions as $providerKey => $providerLabel)
                            <option value="{{ $providerKey }}" @selected($selectedAiProvider === $providerKey)>{{ $providerLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="openrouter-base-url" value="Base URL OpenRouter" />
                        <x-text-input id="openrouter-base-url" name="openrouter_base_url" type="text" :value="old('openrouter_base_url', $openrouterConfig?->base_url ?? config('services.openrouter.base_url'))" />
                    </div>
                    <div>
                        <x-input-label for="openrouter-model" value="Model OpenRouter" />
                        <x-text-input id="openrouter-model" name="openrouter_model" type="text" :value="old('openrouter_model', $openrouterModel)" />
                    </div>
                </div>
                <div>
                    <x-input-label for="openrouter-api-key" value="API Key OpenRouter" />
                    <x-text-input id="openrouter-api-key" name="openrouter_api_key" type="password" value="" placeholder="Biarkan kosong untuk tetap pakai nilai yang tersimpan" />
                </div>
                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="openrouter_enabled" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked($openrouterConfig?->is_active)> Aktifkan OpenRouter</label>

                <label class="inline-flex items-center gap-3 text-sm font-semibold text-slate-700"><input type="checkbox" name="google_calendar_enabled" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(data_get($googleCalendarSettings?->value, 'enabled', false))> Aktifkan setting Google Calendar</label>
                <div>
                    <x-input-label for="google-reminder" value="Reminder Google Calendar (menit)" />
                    <x-text-input id="google-reminder" name="google_default_reminder_minutes" type="number" :value="old('google_default_reminder_minutes', data_get($googleReminderSettings?->value, 'minutes', 60))" />
                </div>

                <div class="flex gap-3">
                    <x-primary-button>Simpan setting</x-primary-button>
                </div>
            </form>

            <div class="mt-3 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('admin.api-settings.sync') }}">
                    @csrf
                    <x-secondary-button type="submit">Sync RSS + market</x-secondary-button>
                </form>

                <form method="POST" action="{{ route('admin.api-settings.test-yahoo-finance') }}">
                    @csrf
                    <x-secondary-button type="submit">Test Yahoo Finance</x-secondary-button>
                </form>

                <form method="POST" action="{{ route('admin.api-settings.test-ai-provider') }}">
                    @csrf
                    <x-secondary-button type="submit">Test AI Provider</x-secondary-button>
                </form>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Status API" description="Informasi cepat untuk admin.">
            <div class="space-y-3 text-sm">
                <div class="rounded-[1.5rem] {{ $rssNewsConfig?->is_active ? 'bg-emerald-50 text-emerald-900' : 'bg-amber-50 text-amber-900' }} p-4">{{ $rssNewsConfig?->is_active ? 'Integrasi RSS News aktif untuk berita IDX.' : 'Integrasi RSS News belum aktif.' }}</div>
                <div class="rounded-[1.5rem] {{ $yahooFinanceConfig?->is_active ? 'bg-emerald-50 text-emerald-900' : 'bg-amber-50 text-amber-900' }} p-4">{{ $yahooFinanceConfig?->is_active ? 'Integrasi Yahoo Finance aktif untuk market tracking investasi.' : 'Integrasi Yahoo Finance belum aktif.' }}</div>
                <div class="rounded-[1.5rem] {{ $openrouterConfig?->is_active ? 'bg-emerald-50 text-emerald-900' : 'bg-amber-50 text-amber-900' }} p-4">{{ $openrouterConfig?->is_active ? 'Provider OpenRouter siap dipakai untuk analisis AI bulanan.' : 'Provider OpenRouter belum aktif.' }}</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Sync RSS terakhir: {{ $rssNewsConfig?->last_synced_at ? \App\Support\FinancePresenter::shortDate($rssNewsConfig->last_synced_at) : 'belum pernah' }}. Sumber aktif: {{ collect(data_get($rssNewsConfig?->settings, 'sources', []))->count() }} feed.</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Sync market data terakhir: {{ $yahooFinanceConfig?->last_synced_at ? \App\Support\FinancePresenter::shortDate($yahooFinanceConfig->last_synced_at) : 'belum pernah' }}. Chart interval saat ini {{ $yahooChartInterval }} dengan {{ $yahooChartPoints }} titik data. Yahoo Finance versi ini tidak membutuhkan token tambahan.</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Provider AI aktif saat ini: {{ $aiProviderOptions[$selectedAiProvider] ?? 'OpenRouter' }}. Model OpenRouter saat ini: {{ $openrouterModel }}. Snapshot insight dibuat sekali per bulan setelah user melewati 1 bulan sejak transaksi pertama, lalu disimpan tanpa regenerate saat halaman direfresh.</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Google client id dari env: {{ config('services.google.client_id') ? 'terdeteksi' : 'belum diisi' }}.</div>
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
