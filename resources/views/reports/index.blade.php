<x-app-layout>
    <x-slot name="title">Laporan Keuangan</x-slot>
    <x-slot name="heading">Laporan Keuangan</x-slot>
    <x-slot name="description">Tarik laporan keuangan lengkap berisi ringkasan, rincian, serta analisis dan saran AI yang sudah tersimpan.</x-slot>

    @php
        $latestPayload = $latestReport?->payload ?? [];
        $latestAnalysis = preg_split('/\r\n|\r|\n/', (string) data_get($latestPayload, 'ai_analysis.analysis', ''));
        $latestRecommendations = preg_split('/\r\n|\r|\n/', (string) data_get($latestPayload, 'ai_analysis.recommendations', ''));
    @endphp

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <x-ui.panel title="Tarik laporan" description="Laporan bisa ditarik kapan saja setelah melewati 1 bulan sejak transaksi pertama user.">
                @if ($eligibility['eligible'])
                    <div class="rounded-[1.5rem] bg-emerald-50 p-4 text-sm text-emerald-900">
                        Laporan sudah aktif. AI menganalisis berdasarkan snapshot bulanan yang tersedia, lalu laporan bisa ditarik kapan saja tanpa menunggu bulan berikutnya.
                    </div>

                    <form method="POST" action="{{ route('reports.store') }}" class="mt-4 grid gap-4">
                        @csrf
                        <div>
                            <x-input-label for="report-title" value="Judul laporan (opsional)" />
                            <x-text-input id="report-title" name="title" type="text" :value="old('title')" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="report-period-start" value="Periode mulai" />
                                <x-text-input id="report-period-start" name="period_start" type="date" :value="old('period_start', now()->subDays(29)->toDateString())" />
                            </div>
                            <div>
                                <x-input-label for="report-period-end" value="Periode akhir" />
                                <x-text-input id="report-period-end" name="period_end" type="date" :value="old('period_end', now()->toDateString())" />
                            </div>
                        </div>
                        <x-primary-button>Tarik laporan</x-primary-button>
                    </form>
                @else
                    <div class="rounded-[1.5rem] bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                        {{ $eligibility['reason'] }}
                        @if (! empty($eligibility['available_on']))
                            Laporan mulai bisa ditarik pada {{ \App\Support\FinancePresenter::shortDate($eligibility['available_on']) }}.
                        @endif
                    </div>
                @endif
            </x-ui.panel>

            <x-ui.panel title="Laporan terbaru" description="Preview cepat laporan terakhir yang tersimpan.">
                @if ($latestReport)
                    <div class="grid gap-4 md:grid-cols-3">
                        <x-ui.stat-card label="Pemasukan" :value="\App\Support\FinancePresenter::money((float) data_get($latestPayload, 'summary.income', 0))" trend="Periode laporan" tone="emerald" />
                        <x-ui.stat-card label="Pengeluaran" :value="\App\Support\FinancePresenter::money((float) data_get($latestPayload, 'summary.expense', 0))" trend="Periode laporan" tone="rose" />
                        <x-ui.stat-card label="Selisih" :value="\App\Support\FinancePresenter::signedMoney((float) data_get($latestPayload, 'summary.net_cashflow', 0))" trend="Net cashflow" tone="slate" />
                    </div>

                    <div class="mt-4 rounded-[1.5rem] bg-slate-50 p-4 text-sm text-slate-600">
                        {{ $latestReport->title }} - periode {{ \App\Support\FinancePresenter::shortDate($latestReport->period_start) }} s.d. {{ \App\Support\FinancePresenter::shortDate($latestReport->period_end) }}.
                    </div>

                    @if (array_filter($latestAnalysis))
                        <div class="mt-4 space-y-3">
                            @foreach (array_filter($latestAnalysis) as $item)
                                <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">{{ ltrim($item, "- \t") }}</div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('reports.show', $latestReport) }}" class="inline-flex items-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Buka laporan lengkap</a>
                        <a href="{{ route('reports.show', $latestReport) }}" target="_blank" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Cetak / Simpan PDF</a>
                        <a href="{{ route('reports.pdf', $latestReport) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Download PDF</a>
                    </div>
                @else
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada laporan yang pernah ditarik.</div>
                @endif
            </x-ui.panel>
        </section>

        <x-ui.panel title="Riwayat laporan" description="Semua laporan yang pernah ditarik user tersimpan di sini dan bisa dibuka kapan saja.">
            <div class="space-y-4">
                @forelse ($reports as $report)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $report->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">Periode {{ \App\Support\FinancePresenter::shortDate($report->period_start) }} - {{ \App\Support\FinancePresenter::shortDate($report->period_end) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Dit tarik {{ \App\Support\FinancePresenter::shortDate($report->generated_at) }}{{ $report->aiFinancialInsight?->provider ? ' - AI: '.$report->aiFinancialInsight->provider : '' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('reports.show', $report) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Buka detail</a>
                                <a href="{{ route('reports.show', $report) }}" target="_blank" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Cetak / PDF</a>
                                <a href="{{ route('reports.pdf', $report) }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Download PDF</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada riwayat laporan.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
