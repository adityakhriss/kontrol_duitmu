<x-app-layout>
    <x-slot name="title">Berita Investasi</x-slot>
    <x-slot name="heading">Feed Berita Investasi</x-slot>
    <x-slot name="description">Headline pasar modal Indonesia dari sumber RSS publik dengan ringkasan, gambar, dan link baca lanjut.</x-slot>

    <div class="space-y-6">
        <x-ui.panel title="Status feed" description="Berita diambil dari agregasi RSS dan disimpan sebagai cache lokal agar tetap cepat dibuka.">
            <div class="grid gap-4 lg:grid-cols-3">
                <div class="rounded-[1.5rem] bg-white p-4">
                    <p class="text-sm text-slate-500">Provider</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $newsConfig?->provider === 'rss_news' ? 'RSS News IDX' : 'Belum aktif' }}</p>
                </div>
                <div class="rounded-[1.5rem] bg-white p-4">
                    <p class="text-sm text-slate-500">Sync terakhir</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ $newsConfig?->last_synced_at ? \App\Support\FinancePresenter::shortDate($newsConfig->last_synced_at) : 'Belum pernah' }}</p>
                </div>
                <div class="rounded-[1.5rem] bg-white p-4">
                    <p class="text-sm text-slate-500">Sumber aktif</p>
                    <p class="mt-2 font-semibold text-slate-950">{{ collect(data_get($newsConfig?->settings, 'sources', []))->count() }} feed</p>
                </div>
            </div>
        </x-ui.panel>

        <div class="grid gap-5 xl:grid-cols-2">
            @forelse ($news as $newsItem)
                <article class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-soft">
                    <div class="grid h-full gap-0 md:grid-cols-[0.95fr_1.05fr]">
                        <div class="relative min-h-[220px] bg-slate-100">
                            @if ($newsItem->image_url)
                                <img src="{{ $newsItem->image_url }}" alt="{{ $newsItem->title }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 via-slate-50 to-emerald-50 text-sm font-semibold text-slate-400">No image</div>
                            @endif
                        </div>

                        <div class="flex flex-col justify-between p-6">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge badge-slate">{{ $newsItem->source ?? 'RSS News' }}</span>
                                    <span class="text-xs text-slate-400">{{ \App\Support\FinancePresenter::shortDate($newsItem->published_at) }}</span>
                                </div>

                                <h2 class="mt-4 text-xl font-bold leading-snug text-slate-950">{{ $newsItem->title }}</h2>
                                <p class="mt-3 text-sm leading-7 text-slate-600">{{ $newsItem->summary ?: 'Ringkasan berita belum tersedia.' }}</p>
                            </div>

                            @if ($newsItem->url)
                                <div class="mt-6">
                                    <a href="{{ $newsItem->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Baca lebih lengkap</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <x-ui.panel title="Belum ada berita" description="Jalankan sync dari panel admin untuk mengisi feed ini.">
                    <p class="text-sm text-slate-500">Headline pasar modal Indonesia akan muncul di sini setelah sinkronisasi RSS pertama berhasil.</p>
                </x-ui.panel>
            @endforelse
        </div>

        <div>{{ $news->links() }}</div>
    </div>
</x-app-layout>
