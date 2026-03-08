@php
    $navigation = $navigation ?? 'user';
    $links = $navigation === 'admin'
        ? [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'description' => 'Ringkasan sistem'],
            ['label' => 'Users', 'route' => 'admin.users', 'description' => 'Kelola akun user'],
            ['label' => 'API Berita', 'route' => 'admin.api-settings', 'description' => 'Alpha Vantage'],
            ['label' => 'Sync Logs', 'route' => 'admin.sync-logs', 'description' => 'Riwayat sinkronisasi'],
          ]
        : [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'description' => 'Ringkasan hari ini'],
            ['label' => 'Saldo', 'route' => 'accounts.index', 'description' => 'Tunai, bank, e-wallet'],
            ['label' => 'Transaksi', 'route' => 'transactions.index', 'description' => 'Pemasukan & pengeluaran'],
            ['label' => 'Tagihan', 'route' => 'bills.index', 'description' => 'Jatuh tempo bulanan'],
            ['label' => 'Goal', 'route' => 'saving-goals.index', 'description' => 'Target tabungan'],
            ['label' => 'Hutang', 'route' => 'debts.index', 'description' => 'Kewajiban aktif'],
            ['label' => 'Investasi', 'route' => 'investments.index', 'description' => 'Portofolio aset'],
            ['label' => 'Berita', 'route' => 'news.index', 'description' => 'Insight pasar'],
            ['label' => 'Analisis', 'route' => 'analysis.index', 'description' => 'Cashflow & tren'],
          ];
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-80 border-r border-white/60 bg-[var(--color-surface)] px-5 py-6 shadow-soft backdrop-blur lg:block">
    <div class="flex h-full flex-col gap-6">
        <div class="flex items-center gap-3 px-2">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-sm font-bold text-white">KD</span>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">{{ $navigation === 'admin' ? 'Admin desk' : 'Money desk' }}</p>
                <p class="font-display text-2xl text-slate-950">Kontrol Duitmu</p>
            </div>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto pr-1">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['route']))
                <a href="{{ route($link['route']) }}" class="app-link {{ $active ? 'app-link-active' : 'app-link-idle' }}">
                    <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-2xl {{ $active ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700' }}">{{ strtoupper(substr($link['label'], 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ $link['label'] }}</span>
                        <span class="mt-0.5 block text-xs {{ $active ? 'text-white/70' : 'text-slate-500' }}">{{ $link['description'] }}</span>
                    </span>
                </a>
            @endforeach
        </nav>

    </div>
</aside>

<div x-cloak x-show="navOpen" class="fixed inset-0 z-50 lg:hidden">
    <div class="absolute inset-0 bg-slate-950/40" @click="navOpen = false"></div>
    <div class="absolute inset-y-0 left-0 flex w-[88vw] max-w-xs flex-col bg-[var(--color-bg)] p-4 shadow-soft">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-slate-500">{{ $navigation === 'admin' ? 'Admin desk' : 'Money desk' }}</p>
                <p class="font-display text-2xl text-slate-950">Kontrol Duitmu</p>
            </div>
            <button type="button" @click="navOpen = false" class="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/70 bg-white/80 text-slate-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <nav class="space-y-2 overflow-y-auto">
            @foreach ($links as $link)
                @php($active = request()->routeIs($link['route']))
                <a href="{{ route($link['route']) }}" class="app-link {{ $active ? 'app-link-active' : 'app-link-idle' }}" @click="navOpen = false">
                    <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-2xl {{ $active ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700' }}">{{ strtoupper(substr($link['label'], 0, 1)) }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold">{{ $link['label'] }}</span>
                        <span class="mt-0.5 block text-xs {{ $active ? 'text-white/70' : 'text-slate-500' }}">{{ $link['description'] }}</span>
                    </span>
                </a>
            @endforeach
        </nav>
    </div>
</div>

<nav class="fixed inset-x-0 bottom-4 z-30 mx-auto flex w-[calc(100%-1.5rem)] max-w-md items-center justify-between rounded-[1.75rem] border border-white/70 bg-white/85 px-3 py-2 shadow-soft backdrop-blur lg:hidden">
    @foreach (array_slice($links, 0, min(5, count($links))) as $link)
        @php($active = request()->routeIs($link['route']))
        <a href="{{ route($link['route']) }}" class="flex min-w-[3.4rem] flex-col items-center gap-1 rounded-2xl px-2 py-2 text-xs font-semibold {{ $active ? 'bg-slate-950 text-white' : 'text-slate-500' }}">
            <span>{{ strtoupper(substr($link['label'], 0, 1)) }}</span>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>
