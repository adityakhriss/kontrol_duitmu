@php
    $navigation = $navigation ?? 'user';
    $links = $navigation === 'admin'
        ? [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'description' => 'Ringkasan sistem'],
            ['label' => 'Users', 'route' => 'admin.users', 'description' => 'Kelola akun user'],
            ['label' => 'API', 'route' => 'admin.api-settings', 'description' => 'Provider & koneksi'],
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
            ['label' => 'Laporan', 'route' => 'reports.index', 'description' => 'Ringkasan keuangan'],
          ];
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-80 border-r border-white/60 bg-[var(--color-surface)] px-5 py-6 shadow-soft backdrop-blur lg:block">
    <div class="flex h-full flex-col gap-6">
        <div class="flex items-center gap-3 px-2">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-soft">
                <x-application-logo class="h-8 w-8 text-white" />
            </span>
            <div>
                <p class="font-display text-2xl text-slate-950">Kontrol Duitmu</p>
            </div>
        </div>

        <nav class="flex-1 space-y-2 overflow-y-auto pr-1">
            @foreach ($links as $link)
                @php
                    $active = $link['route'] === 'reports.index'
                        ? request()->routeIs('reports.*')
                        : request()->routeIs($link['route']);
                    $iconClasses = $active ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700';
                @endphp
                <a href="{{ route($link['route']) }}" class="app-link {{ $active ? 'app-link-active' : 'app-link-idle' }}">
                    <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-2xl {{ $iconClasses }}">
                        @switch($link['route'])
                            @case('dashboard')
                            @case('admin.dashboard')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10" />
                                </svg>
                                @break
                            @case('accounts.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="3" y="6" width="18" height="12" rx="2" stroke-width="1.8" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M16 14h2" />
                                </svg>
                                @break
                            @case('transactions.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h11M7 12h11M7 17h11M4 7h.01M4 12h.01M4 17h.01" />
                                </svg>
                                @break
                            @case('bills.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" />
                                </svg>
                                @break
                            @case('saving-goals.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s-6-4.35-8.5-8A5.3 5.3 0 0 1 12 6.5 5.3 5.3 0 0 1 20.5 13C18 16.65 12 21 12 21Z" />
                                </svg>
                                @break
                            @case('debts.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18M8.5 7.5h5a2.5 2.5 0 0 1 0 5h-3a2.5 2.5 0 0 0 0 5h5" />
                                </svg>
                                @break
                            @case('investments.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M7 16V9M12 16V5M17 16v-7" />
                                </svg>
                                @break
                            @case('news.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h11a2 2 0 0 1 2 2v8H7a2 2 0 0 1-2-2V7Zm13 10a2 2 0 0 0 2-2V9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h7M8 13h7" />
                                </svg>
                                @break
                            @case('analysis.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M7 16l3-4 3 2 4-6" />
                                </svg>
                                @break
                            @case('reports.index')
                            @case('reports.show')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3v5h5M9 13h6M9 17h6" />
                                </svg>
                                @break
                            @case('admin.users')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 9v-1a4 4 0 0 0-3-3.87M15 4.13a3 3 0 0 1 0 5.74" />
                                </svg>
                                @break
                            @case('admin.api-settings')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Zm7-3.5 2-1-2-1-.5-2.1-2.1-.5-1-2-1 2-2.1.5L10 10 8 11l2 1 .5 2.1 2.1.5 1 2 1-2 2.1-.5L19 12Z" />
                                </svg>
                                @break
                            @case('admin.sync-logs')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5l3 3M21 12a9 9 0 1 1-3-6.7" />
                                </svg>
                                @break
                            @default
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="8" stroke-width="1.8" />
                                </svg>
                        @endswitch
                    </span>
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
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-white shadow-soft">
                    <x-application-logo class="h-7 w-7 text-white" />
                </span>
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
                @php
                    $active = $link['route'] === 'reports.index'
                        ? request()->routeIs('reports.*')
                        : request()->routeIs($link['route']);
                    $iconClasses = $active ? 'bg-white/10 text-white' : 'bg-slate-100 text-slate-700';
                @endphp
                <a href="{{ route($link['route']) }}" class="app-link {{ $active ? 'app-link-active' : 'app-link-idle' }}" @click="navOpen = false">
                    <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-2xl {{ $iconClasses }}">
                        @switch($link['route'])
                            @case('dashboard')
                            @case('admin.dashboard')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10" /></svg>
                                @break
                            @case('accounts.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M16 14h2" /></svg>
                                @break
                            @case('transactions.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h11M7 12h11M7 17h11M4 7h.01M4 12h.01M4 17h.01" /></svg>
                                @break
                            @case('bills.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                                @break
                            @case('saving-goals.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s-6-4.35-8.5-8A5.3 5.3 0 0 1 12 6.5 5.3 5.3 0 0 1 20.5 13C18 16.65 12 21 12 21Z" /></svg>
                                @break
                            @case('debts.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18M8.5 7.5h5a2.5 2.5 0 0 1 0 5h-3a2.5 2.5 0 0 0 0 5h5" /></svg>
                                @break
                            @case('investments.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M7 16V9M12 16V5M17 16v-7" /></svg>
                                @break
                            @case('news.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 7h11a2 2 0 0 1 2 2v8H7a2 2 0 0 1-2-2V7Zm13 10a2 2 0 0 0 2-2V9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h7M8 13h7" /></svg>
                                @break
                            @case('analysis.index')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19h16M7 16l3-4 3 2 4-6" /></svg>
                                @break
                            @case('reports.index')
                            @case('reports.show')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3v5h5M9 13h6M9 17h6" /></svg>
                                @break
                            @case('admin.users')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M10 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm8 9v-1a4 4 0 0 0-3-3.87M15 4.13a3 3 0 0 1 0 5.74" /></svg>
                                @break
                            @case('admin.api-settings')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15.5A3.5 3.5 0 1 0 12 8.5a3.5 3.5 0 0 0 0 7Zm7-3.5 2-1-2-1-.5-2.1-2.1-.5-1-2-1 2-2.1.5L10 10 8 11l2 1 .5 2.1 2.1.5 1 2 1-2 2.1-.5L19 12Z" /></svg>
                                @break
                            @case('admin.sync-logs')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v5l3 3M21 12a9 9 0 1 1-3-6.7" /></svg>
                                @break
                            @default
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="1.8" /></svg>
                        @endswitch
                    </span>
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
            <span>
                @switch($link['route'])
                    @case('dashboard')
                    @case('admin.dashboard')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l9-8 9 8M5 10v10h14V10" /></svg>
                        @break
                    @case('accounts.index')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="12" rx="2" stroke-width="1.8" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M16 14h2" /></svg>
                        @break
                    @case('transactions.index')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h11M7 12h11M7 17h11M4 7h.01M4 12h.01M4 17h.01" /></svg>
                        @break
                    @case('bills.index')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3v3M17 3v3M4 9h16M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" /></svg>
                        @break
                    @case('saving-goals.index')
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21s-6-4.35-8.5-8A5.3 5.3 0 0 1 12 6.5 5.3 5.3 0 0 1 20.5 13C18 16.65 12 21 12 21Z" /></svg>
                        @break
                    @default
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="1.8" /></svg>
                @endswitch
            </span>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>
