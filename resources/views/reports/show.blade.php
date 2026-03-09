<x-app-layout>
    <x-slot name="title">Detail Laporan</x-slot>
    <x-slot name="heading">{{ $report->title }}</x-slot>
    <x-slot name="description">Periode {{ \App\Support\FinancePresenter::shortDate($report->period_start) }} - {{ \App\Support\FinancePresenter::shortDate($report->period_end) }}.</x-slot>
    <x-slot name="actions">
        <div class="flex flex-wrap gap-3 print:hidden">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Kembali</a>
            <button type="button" onclick="window.print()" class="inline-flex items-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Cetak / Simpan PDF</button>
            <a href="{{ route('reports.pdf', $report) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Download PDF</a>
        </div>
    </x-slot>

    @php
        $payload = $report->payload ?? [];
        $analysisItems = preg_split('/\r\n|\r|\n/', (string) data_get($payload, 'ai_analysis.analysis', ''));
        $recommendationItems = preg_split('/\r\n|\r|\n/', (string) data_get($payload, 'ai_analysis.recommendations', ''));
    @endphp

    <div class="report-print-layout space-y-6">
        <section class="report-print-header hidden print:block">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Laporan Keuangan</p>
                <h1 class="mt-2 text-3xl font-bold text-slate-950">{{ $report->title }}</h1>
                <p class="mt-2 text-sm text-slate-600">Periode {{ \App\Support\FinancePresenter::shortDate($report->period_start) }} - {{ \App\Support\FinancePresenter::shortDate($report->period_end) }}</p>
            </div>
            <div class="rounded-[1.25rem] border border-slate-200 bg-slate-50 px-4 py-3 text-right text-sm text-slate-600">
                <p>Dibuat {{ \App\Support\FinancePresenter::shortDate($report->generated_at) }}</p>
                <p>{{ $report->aiFinancialInsight?->provider ? 'AI: '.$report->aiFinancialInsight->provider : 'Tanpa snapshot AI' }}</p>
            </div>
        </section>

        <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600 print:hidden">
            Gunakan tombol `Cetak / Simpan PDF` untuk menyimpan laporan ini sebagai PDF dari browser.
        </div>

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.stat-card label="Pemasukan" :value="\App\Support\FinancePresenter::money((float) data_get($payload, 'summary.income', 0))" trend="Periode laporan" tone="emerald" />
            <x-ui.stat-card label="Pengeluaran" :value="\App\Support\FinancePresenter::money((float) data_get($payload, 'summary.expense', 0))" trend="Periode laporan" tone="rose" />
            <x-ui.stat-card label="Selisih" :value="\App\Support\FinancePresenter::signedMoney((float) data_get($payload, 'summary.net_cashflow', 0))" trend="Net cashflow" tone="slate" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
            <x-ui.panel title="Ringkasan laporan" description="Rangkuman lengkap kondisi keuangan pada periode laporan.">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">Total transaksi<br><span class="mt-2 block text-lg font-bold text-slate-950">{{ (int) data_get($payload, 'summary.transaction_count', 0) }}</span></div>
                    <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">Saldo likuid<br><span class="mt-2 block text-lg font-bold text-slate-950">{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.liquid_balance_total', 0)) }}</span></div>
                    <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">Tagihan aktif<br><span class="mt-2 block text-lg font-bold text-slate-950">{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.upcoming_bills_total', 0)) }}</span></div>
                    <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">Nilai investasi<br><span class="mt-2 block text-lg font-bold text-slate-950">{{ \App\Support\FinancePresenter::money((float) data_get($payload, 'summary.investment_value_total', 0)) }}</span></div>
                </div>
            </x-ui.panel>

            <x-ui.panel title="AI Analisis & Saran" description="Menggunakan snapshot AI bulanan yang tersedia pada saat laporan ditarik.">
                <div class="space-y-4">
                    <div class="rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">
                        Snapshot AI: {{ data_get($payload, 'ai_analysis.snapshot_month') ? \App\Support\FinancePresenter::shortDate(data_get($payload, 'ai_analysis.snapshot_month')) : 'Belum tersedia' }}{{ data_get($payload, 'ai_analysis.provider') ? ' - '.data_get($payload, 'ai_analysis.provider') : '' }}
                    </div>

                    @forelse (array_filter($analysisItems) as $item)
                        <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">{{ ltrim($item, "- \t") }}</div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada analisis AI yang tersimpan pada laporan ini.</div>
                    @endforelse

                    @if (array_filter($recommendationItems))
                        <div class="rounded-[1.5rem] border border-emerald-100 bg-white p-5">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Saran AI</p>
                            <div class="mt-3 space-y-3">
                                @foreach (array_filter($recommendationItems) as $item)
                                    <div class="rounded-[1.25rem] bg-emerald-50 p-4 text-sm leading-7 text-emerald-900">{{ ltrim($item, "- \t") }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Kategori pengeluaran" description="Rincian kategori pengeluaran terbesar selama periode laporan.">
            <div class="space-y-3">
                @forelse (data_get($payload, 'expense_by_category', []) as $item)
                    <div class="rounded-[1.5rem] bg-white p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $item['category'] }}</p>
                                <p class="text-sm text-slate-500">{{ $item['count'] }} transaksi</p>
                            </div>
                            <span class="badge badge-amber">{{ \App\Support\FinancePresenter::money((float) $item['amount']) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada data kategori pengeluaran pada laporan ini.</div>
                @endforelse
            </div>
        </x-ui.panel>

        <x-ui.panel title="Transaksi terpilih" description="Maksimal 25 transaksi terbaru/terbesar untuk memberi konteks laporan.">
            <div class="space-y-3">
                @forelse (data_get($payload, 'transactions', []) as $transaction)
                    <div class="rounded-[1.5rem] bg-white p-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $transaction['category'] ?? 'Tanpa kategori' }} - {{ $transaction['account'] ?? '-' }}</p>
                                <p class="text-sm text-slate-500">{{ \App\Support\FinancePresenter::shortDate($transaction['date']) }} - {{ $transaction['notes'] ?: 'Tanpa catatan' }}</p>
                            </div>
                            <span class="badge {{ ($transaction['type'] ?? null) === 'income' ? 'badge-emerald' : 'badge-amber' }}">{{ \App\Support\FinancePresenter::money((float) ($transaction['amount'] ?? 0)) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada transaksi yang tersimpan pada laporan ini.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
