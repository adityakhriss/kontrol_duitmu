<x-app-layout>
    <x-slot name="title">Analisis</x-slot>
    <x-slot name="heading">Analisis Keuangan</x-slot>
    <x-slot name="description">Preview visual untuk cashflow, kategori pengeluaran, saldo, hutang, goal, dan investasi.</x-slot>

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

        <x-ui.panel title="Insight otomatis" description="Kumpulan insight pendek untuk dashboard analitis.">
            <div class="space-y-3">
                <div class="rounded-[1.5rem] bg-emerald-50 p-4 text-sm leading-7 text-emerald-900">Pengeluaran makan meningkat dibanding bulan lalu, tetapi cashflow total masih sehat.</div>
                <div class="rounded-[1.5rem] bg-slate-100 p-4 text-sm leading-7 text-slate-700">Saldo e-wallet menjadi akun paling aktif untuk transaksi harian.</div>
                <div class="rounded-[1.5rem] bg-amber-50 p-4 text-sm leading-7 text-amber-900">Tiga tagihan mendekati jatuh tempo sehingga saldo bank perlu diprioritaskan.</div>
                <div class="rounded-[1.5rem] bg-white p-4 text-sm leading-7 text-slate-600">Nilai portofolio investasi masih bertumbuh dan belum menekan likuiditas utama.</div>
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
