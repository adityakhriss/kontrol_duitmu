<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="heading">Dashboard Keuangan</x-slot>
    <x-slot name="description">Ringkasan saldo, cashflow, tagihan, goal, investasi, dan berita investasi dalam satu layar.</x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 xl:grid-cols-[1.25fr_0.75fr]">
            <div class="surface-panel overflow-hidden bg-slate-950 p-6 text-white sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="section-kicker text-white/50">Total aset likuid</p>
                        <h2 class="mt-3 text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $summary['total_liquid'] }}</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-7 text-white/70">Saldo terkonsolidasi dari tiga akun utama untuk membantu keputusan harian tanpa perlu membuka banyak halaman.</p>
                    </div>
                    <div class="grid gap-3 rounded-[1.75rem] bg-white/10 p-4 text-sm sm:min-w-[18rem]">
                        <div class="flex items-center justify-between"><span class="text-white/70">Pemasukan bulan ini</span><span class="font-semibold text-emerald-300">{{ $summary['income'] }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-white/70">Pengeluaran bulan ini</span><span class="font-semibold text-rose-300">{{ $summary['expense'] }}</span></div>
                        <div class="flex items-center justify-between"><span class="text-white/70">Cashflow bersih</span><span class="font-semibold text-white">{{ $summary['net_cashflow'] }}</span></div>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    @foreach ($accounts as $account)
                        <div class="rounded-[1.75rem] bg-white/10 p-5">
                            <p class="text-xs uppercase tracking-[0.24em] text-white/45">{{ $account->name }}</p>
                            <p class="mt-3 text-2xl font-bold">{{ \App\Support\FinancePresenter::money($account->balance) }}</p>
                            <p class="mt-2 text-sm text-white/65">{{ ucfirst(str_replace('_', ' ', $account->type)) }} account aktif untuk transaksi utama.</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <x-ui.panel title="Quick actions" description="Aksi yang paling sering dipakai user.">
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <a href="{{ route('transactions.create') }}" class="rounded-[1.5rem] bg-emerald-50 p-4 transition hover:-translate-y-0.5">
                        <p class="font-semibold text-emerald-900">Tambah transaksi</p>
                        <p class="mt-1 text-sm text-emerald-700">Catat pemasukan atau pengeluaran baru.</p>
                    </a>
                    <a href="{{ route('accounts.transfer') }}" class="rounded-[1.5rem] bg-slate-100 p-4 transition hover:-translate-y-0.5">
                        <p class="font-semibold text-slate-900">Transfer saldo</p>
                        <p class="mt-1 text-sm text-slate-600">Pindahkan dana antar akun internal.</p>
                    </a>
                    <a href="{{ route('bills.index') }}" class="rounded-[1.5rem] bg-amber-50 p-4 transition hover:-translate-y-0.5">
                        <p class="font-semibold text-amber-900">Bayar tagihan</p>
                        <p class="mt-1 text-sm text-amber-700">Lihat tagihan terdekat dan jadwalnya.</p>
                    </a>
                    <a href="{{ route('saving-goals.index') }}" class="rounded-[1.5rem] bg-rose-50 p-4 transition hover:-translate-y-0.5">
                        <p class="font-semibold text-rose-900">Setor ke goal</p>
                        <p class="mt-1 text-sm text-rose-700">Dorong progres target tabungan aktif.</p>
                    </a>
                </div>
            </x-ui.panel>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Pemasukan" :value="$summary['income']" trend="Periode bulan berjalan" tone="emerald" />
            <x-ui.stat-card label="Pengeluaran" :value="$summary['expense']" trend="Periode bulan berjalan" tone="rose" />
            <x-ui.stat-card label="Tagihan aktif" :value="$summary['active_bills'].' item'" trend="Perlu dipantau di minggu ini" tone="amber" />
            <x-ui.stat-card label="Nilai investasi" :value="$summary['investment_value']" trend="Akumulasi portofolio aktif" tone="slate" />
        </section>

        <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <x-ui.panel title="Tagihan dan pengingat" description="Fokus pada kewajiban yang paling dekat.">
                <div class="space-y-3">
                    @forelse ($upcomingBills as $bill)
                        <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $bill->name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Bayar dari {{ $bill->paymentAccount?->name ?? 'Belum dipilih' }} - {{ \App\Support\FinancePresenter::money($bill->amount) }}</p>
                                </div>
                                <span class="badge badge-amber">{{ \App\Support\FinancePresenter::shortDate($bill->due_date, '-') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">Belum ada tagihan aktif.</div>
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel title="Goal tabungan" description="Target aktif yang perlu dipacu.">
                <div class="space-y-5">
                    @forelse ($goals as $goal)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $goal->name }}</p>
                                    <p class="text-sm text-slate-500">{{ \App\Support\FinancePresenter::money($goal->current_amount) }} / {{ \App\Support\FinancePresenter::money($goal->target_amount) }}</p>
                                </div>
                                <span class="text-sm font-semibold text-slate-700">{{ (int) round(($goal->current_amount / max(1, $goal->target_amount)) * 100) }}%</span>
                            </div>
                            <x-ui.progress-bar :value="(int) round(($goal->current_amount / max(1, $goal->target_amount)) * 100)" />
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">Belum ada goal tabungan aktif.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Berita investasi" description="Ringkasan headline terbaru dari feed RSS pasar modal Indonesia.">
                <div class="space-y-3">
                    @forelse ($news as $newsItem)
                        <article class="rounded-[1.5rem] border border-slate-200 bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="badge badge-slate">{{ $newsItem->category ?? 'Berita' }}</span>
                                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Preview</span>
                            </div>
                            <h3 class="mt-3 text-base font-semibold text-slate-950">{{ $newsItem->title }}</h3>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">Belum ada cache berita investasi.</div>
                    @endforelse
                </div>
            </x-ui.panel>

            <x-ui.panel title="Analisis cepat" description="Insight sederhana untuk membantu keputusan berikutnya.">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] bg-emerald-50 p-4 text-emerald-900">
                        <p class="font-semibold">Cashflow aman</p>
                        <p class="mt-2 text-sm leading-7">{{ $insights['cashflow'] }}</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-amber-50 p-4 text-amber-900">
                        <p class="font-semibold">Tagihan padat minggu ini</p>
                        <p class="mt-2 text-sm leading-7">{{ $insights['bill'] }}</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-slate-100 p-4 text-slate-800">
                        <p class="font-semibold">Akun dominan</p>
                        <p class="mt-2 text-sm leading-7">{{ $insights['wallet'] }}</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-rose-50 p-4 text-rose-900">
                        <p class="font-semibold">Kategori utama</p>
                        <p class="mt-2 text-sm leading-7">{{ $insights['category'] }}</p>
                    </div>
                </div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
