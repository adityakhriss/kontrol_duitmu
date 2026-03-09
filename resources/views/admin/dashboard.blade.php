<x-app-layout>
    <x-slot name="title">Admin Dashboard</x-slot>
    <x-slot name="heading">Dashboard Admin</x-slot>
    <x-slot name="description">Monitor user, integrasi RSS berita, market data, dan sinkronisasi sistem dari satu panel.</x-slot>
    <x-slot name="navigation">admin</x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total user" :value="(string) $totalUsers" trend="Total akun terdaftar" tone="slate" />
            <x-ui.stat-card label="User aktif" :value="(string) $activeUsers" :trend="($totalUsers - $activeUsers).' akun nonaktif'" tone="emerald" />
            <x-ui.stat-card label="RSS News" :value="$apiConfig?->is_active ? 'Aktif' : 'Nonaktif'" :trend="'Sync tiap '.($apiConfig?->sync_interval_minutes ?? 60).' menit'" tone="amber" />
            <x-ui.stat-card label="Google Calendar" value="Ready" trend="Feature toggle tersedia" tone="slate" />
        </section>

        <section class="grid gap-4 xl:grid-cols-[1fr_1fr]">
            <x-ui.panel title="Shortcut admin" description="Akses cepat ke menu inti.">
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('admin.users') }}" class="rounded-[1.5rem] bg-white p-4 font-semibold text-slate-950">Kelola users</a>
                    <a href="{{ route('admin.api-settings') }}" class="rounded-[1.5rem] bg-white p-4 font-semibold text-slate-950">Setting API</a>
                    <a href="{{ route('admin.sync-logs') }}" class="rounded-[1.5rem] bg-white p-4 font-semibold text-slate-950">Lihat sync logs</a>
                    <div class="rounded-[1.5rem] bg-white p-4 font-semibold text-slate-950">Toggle Google Calendar</div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="Sync terbaru" description="Preview log singkat untuk monitoring.">
                <div class="space-y-3 text-sm">
                    @foreach ($latestLogs as $log)
                        <div class="rounded-[1.5rem] bg-white p-4"><p class="font-semibold text-slate-950">{{ \App\Support\FinancePresenter::shortDate($log->created_at) }}</p><p class="mt-1 text-slate-500">{{ ucfirst(str_replace('_', ' ', $log->provider)) }} - {{ $log->message }}</p></div>
                    @endforeach
                </div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
