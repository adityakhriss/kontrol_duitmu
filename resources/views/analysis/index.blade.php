<x-app-layout>
    <x-slot name="title">Analisis</x-slot>
    <x-slot name="heading">Analisis Keuangan</x-slot>
    <x-slot name="description">Analisis visual ditambah insight dan saran AI yang disimpan bulanan dari data nyata sistem.</x-slot>

    @php
        $aiInsight = $aiInsightResult['insight'] ?? null;
        $aiEligibility = $aiInsightResult['eligibility'] ?? ['eligible' => false, 'reason' => null, 'months_available' => 0, 'transaction_count' => 0];
        $analysisItems = $aiInsight?->analysis ? preg_split('/\r\n|\r|\n/', $aiInsight->analysis) : [];
        $recommendationItems = $aiInsight?->recommendations ? preg_split('/\r\n|\r|\n/', $aiInsight->recommendations) : [];
    @endphp

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-6">
            <x-ui.panel title="Cashflow" description="Perbandingan cepat dengan periode sebelumnya.">
                <div class="grid gap-4 md:grid-cols-3">
                    <x-ui.stat-card label="Pemasukan" :value="$cashflow['income']" trend="Total tercatat" tone="emerald" />
                    <x-ui.stat-card label="Pengeluaran" :value="$cashflow['expense']" trend="Total tercatat" tone="rose" />
                    <x-ui.stat-card label="Selisih" :value="$cashflow['net']" trend="Hasil akhir cashflow" tone="slate" />
                </div>
            </x-ui.panel>

            <x-ui.panel title="Kategori terbesar" description="Panggung utama untuk chart kategori nanti.">
                <div class="space-y-3">
                    @forelse ($categoryBreakdown as $row)
                        <div>
                            <div class="mb-2 flex items-center justify-between"><span class="font-semibold text-slate-900">{{ $row['label'] }}</span><span class="text-sm text-slate-500">{{ $row['value'] }}%</span></div>
                            <x-ui.progress-bar :value="$row['value']" tone="amber" />
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada cukup data untuk analisis kategori.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </div>

        <x-ui.panel title="Insight AI" description="Snapshot bulanan disimpan sekali per bulan dan tidak dibuat ulang saat halaman direfresh.">
            <div class="space-y-4">
                @if ($aiInsight)
                    <div class="rounded-[1.5rem] bg-emerald-50 p-4 text-sm text-emerald-900">
                        Insight bulan {{ optional($aiInsight->snapshot_month)->translatedFormat('F Y') }} sudah tersimpan. Data analisis mencakup periode {{ optional($aiInsight->analysis_period_start)->translatedFormat('d M Y') }} - {{ optional($aiInsight->analysis_period_end)->translatedFormat('d M Y') }}.
                    </div>

                    <div class="space-y-3">
                        @foreach (array_filter($analysisItems) as $item)
                            <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">{{ ltrim($item, "- \t") }}</div>
                        @endforeach
                    </div>

                    <div class="rounded-[1.5rem] border border-emerald-100 bg-white p-5">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Saran AI</p>
                        <div class="mt-3 space-y-3">
                            @foreach (array_filter($recommendationItems) as $item)
                                <div class="rounded-[1.25rem] bg-emerald-50 p-4 text-sm leading-7 text-emerald-900">{{ ltrim($item, "- \t") }}</div>
                            @endforeach
                        </div>
                    </div>
                @elseif (($aiInsightResult['status'] ?? null) === 'ineligible')
                    <div class="rounded-[1.5rem] bg-amber-50 p-4 text-sm leading-7 text-amber-900">
                        {{ $aiEligibility['reason'] }} Saat ini data tersedia {{ $aiEligibility['transaction_count'] }} transaksi dan {{ $aiEligibility['months_available'] }} bulan penggunaan.
                        @if (! empty($aiEligibility['available_on']))
                            AI mulai bisa menganalisis pada {{ \App\Support\FinancePresenter::shortDate($aiEligibility['available_on']) }}.
                        @endif
                    </div>
                @elseif (($aiInsightResult['status'] ?? null) === 'unavailable')
                    <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">
                        Integrasi AI provider belum aktif di panel admin, jadi insight AI belum bisa dibuat untuk bulan ini.
                    </div>
                @elseif (($aiInsightResult['status'] ?? null) === 'failed')
                    <div class="rounded-[1.5rem] bg-rose-50 p-4 text-sm leading-7 text-rose-800">
                        Gagal membuat insight AI bulan ini: {{ $aiInsightResult['message'] ?? 'Terjadi kendala saat menghubungi AI provider.' }}
                    </div>
                @else
                    <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">
                        Insight AI akan dibuat otomatis sekali per bulan dan disimpan sebagai snapshot setelah melewati 1 bulan sejak transaksi pertama user.
                        @if (! empty($aiEligibility['available_on']))
                            Jadwal analisis pertama: {{ \App\Support\FinancePresenter::shortDate($aiEligibility['available_on']) }}.
                        @endif
                    </div>
                @endif
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
