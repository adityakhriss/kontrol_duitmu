<x-app-layout>
    <x-slot name="title">Berita Investasi</x-slot>
    <x-slot name="heading">Feed Berita Investasi</x-slot>
    <x-slot name="description">Halaman daftar berita yang akan terhubung ke Alpha Vantage dengan cache dan fallback.</x-slot>

    <div class="space-y-6">
        <x-ui.panel title="Filter berita" description="UI awal untuk kategori, keyword, dan status sinkronisasi API.">
            <div class="grid gap-4 lg:grid-cols-[0.9fr_0.9fr_1.2fr]">
                <select class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"><option>{{ $apiConfig?->default_category ?? 'Semua kategori' }}</option></select>
                <select class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"><option>{{ $apiConfig?->is_active ? 'API aktif' : 'API belum aktif' }}</option></select>
                <x-text-input type="text" value="cari sentimen pasar" />
            </div>
        </x-ui.panel>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse ($news as $newsItem)
                <x-ui.panel :title="$newsItem->title" description="Sumber asli akan dibuka lewat link eksternal.">
                    <x-slot name="aside"><span class="badge badge-slate">{{ $newsItem->category ?? 'News' }}</span></x-slot>
                    <p class="text-sm leading-7 text-slate-600">{{ $newsItem->summary ?: 'Konten berita disimpan sebagai cache lokal agar user tetap bisa membaca artikel terakhir saat API eksternal sedang gagal.' }}</p>
                    @if ($newsItem->url)
                        <a href="{{ $newsItem->url }}" target="_blank" class="mt-4 inline-flex text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka sumber</a>
                    @endif
                </x-ui.panel>
            @empty
                <x-ui.panel title="Belum ada berita" description="Jalankan sync dari panel admin untuk mengisi feed ini.">
                    <p class="text-sm text-slate-500">Cache berita investasi akan muncul di sini setelah sinkronisasi pertama berhasil.</p>
                </x-ui.panel>
            @endforelse
        </div>

        <div>{{ $news->links() }}</div>
    </div>
</x-app-layout>
