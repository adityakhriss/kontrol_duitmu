<x-app-layout>
    <x-slot name="title">Sync Logs</x-slot>
    <x-slot name="heading">Log Sinkronisasi</x-slot>
    <x-slot name="description">Tampilan log untuk fetch berita, tes koneksi, dan integrasi terjadwal.</x-slot>
    <x-slot name="navigation">admin</x-slot>

    <x-ui.panel title="Riwayat sinkronisasi" description="Representasi awal untuk api_sync_logs.">
        <div class="space-y-3">
            @foreach ($logs as $log)
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between gap-4">
                        <p class="font-semibold text-slate-950">{{ \App\Support\FinancePresenter::shortDate($log->created_at) }}</p>
                        <span class="badge {{ $log->status === 'success' ? 'badge-emerald' : 'badge-amber' }}">{{ ucfirst($log->status) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-slate-500">{{ ucfirst(str_replace('_', ' ', $log->provider)) }} - {{ $log->message }}</p>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </x-ui.panel>
</x-app-layout>
