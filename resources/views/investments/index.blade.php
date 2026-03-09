<x-app-layout>
    <x-slot name="title">Investasi</x-slot>
    <x-slot name="heading">Portofolio Investasi</x-slot>
    <x-slot name="description">Tambah aset, lacak market symbol, dan pantau valuasi portofolio dari transaksi plus market data.</x-slot>

    @php
        $buildChartPoints = function (array $values): string {
            if (count($values) < 2) {
                return '';
            }

            $prices = collect($values)
                ->map(fn ($item) => (float) ($item['close'] ?? $item['price'] ?? 0))
                ->filter(fn ($value) => $value > 0)
                ->values();

            if ($prices->count() < 2) {
                return '';
            }

            $min = $prices->min();
            $max = $prices->max();
            $range = max($max - $min, 1);
            $width = 260;
            $height = 90;

            return $prices->map(function ($price, $index) use ($prices, $min, $range, $width, $height) {
                $x = $prices->count() > 1 ? ($index / ($prices->count() - 1)) * $width : 0;
                $y = $height - ((($price - $min) / $range) * $height);

                return round($x, 2).','.round($y, 2);
            })->implode(' ');
        };
    @endphp

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-3">
            <x-ui.stat-card label="Total modal" :value="\App\Support\FinancePresenter::money($investments->sum('total_cost'))" trend="Dana yang masih tertanam" tone="slate" />
            <x-ui.stat-card label="Nilai saat ini" :value="\App\Support\FinancePresenter::money($investments->sum('current_value'))" trend="Portofolio aktif" tone="emerald" />
            <x-ui.stat-card label="Gain/Loss" :value="\App\Support\FinancePresenter::signedMoney($investments->sum('current_value') - $investments->sum('total_cost'))" trend="Selisih valuasi" tone="amber" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Tambah aset investasi" description="Buat aset baru beserta pembelian awal dan market symbol agar bisa dilacak otomatis.">
                <form method="POST" action="{{ route('investments.store') }}" class="grid gap-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="investment-name" value="Nama aset" />
                            <x-text-input id="investment-name" name="name" type="text" :value="old('name')" />
                        </div>
                        <div>
                            <x-input-label for="investment-ticker" value="Ticker" />
                            <x-text-input id="investment-ticker" name="ticker" type="text" :value="old('ticker')" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="investment-market-symbol" value="Market symbol" />
                            <x-text-input id="investment-market-symbol" name="market_symbol" type="text" :value="old('market_symbol')" placeholder="Contoh: BBCA" />
                        </div>
                        <div>
                            <x-input-label for="investment-market-exchange" value="Market exchange" />
                            <x-text-input id="investment-market-exchange" name="market_exchange" type="text" :value="old('market_exchange')" placeholder="Contoh: IDX" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="investment-type" value="Jenis aset" />
                            <x-text-input id="investment-type" name="type" type="text" :value="old('type')" />
                        </div>
                        <div>
                            <x-input-label for="investment-platform" value="Platform" />
                            <x-text-input id="investment-platform" name="platform" type="text" :value="old('platform')" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="investment-account" value="Bayar dari akun" />
                            <select id="investment-account" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="investment-date" value="Tanggal beli" />
                            <x-text-input id="investment-date" name="transaction_date" type="date" :value="old('transaction_date', now()->toDateString())" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="investment-units" value="Unit" />
                            <x-text-input id="investment-units" name="units" type="number" step="0.00000001" min="0" :value="old('units')" />
                        </div>
                        <div>
                            <x-input-label for="investment-buy-price" value="Harga beli" />
                            <x-text-input id="investment-buy-price" name="buy_price" type="number" step="0.01" min="0" :value="old('buy_price')" />
                        </div>
                        <div></div>
                    </div>
                    <div>
                        <x-input-label for="investment-notes" value="Catatan" />
                        <textarea id="investment-notes" name="notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                    <x-primary-button>Tambah aset</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Ringkasan portofolio" description="Valuasi sekarang bisa dibantu market data untuk aset yang memiliki market symbol.">
                <div class="space-y-3">
                    @forelse ($investments as $asset)
                        <div class="rounded-[1.5rem] bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $asset->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $asset->type }} - {{ $asset->platform ?: 'Tanpa platform' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-emerald">{{ \App\Support\FinancePresenter::money($asset->current_value) }}</span>
                                    <p class="mt-2 text-xs {{ (float) $asset->market_change_percent >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format((float) $asset->market_change_percent, 2) }}%</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada aset investasi.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Aset investasi" description="Chart dan valuasi market ditampilkan untuk semua aset yang punya market symbol valid.">
            <div class="grid gap-4 xl:grid-cols-2">
                @forelse ($investments as $asset)
                    @php
                        $assetMarket = $marketData[$asset->id] ?? null;
                        $chartValues = data_get($assetMarket, 'chart.values', []);
                        $chartPoints = $buildChartPoints($chartValues);
                        $hasMarketTracking = filled($asset->market_symbol);
                    @endphp

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                        <div class="grid gap-5 lg:grid-cols-[1.05fr_0.95fr]">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $asset->name }}</p>
                                <p class="text-sm text-slate-500">{{ $asset->type }}{{ $asset->ticker ? ' - '.$asset->ticker : '' }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-semibold">
                                    @if ($hasMarketTracking)
                                        <span class="badge badge-emerald">{{ $asset->market_symbol }}{{ $asset->market_exchange ? ':'.$asset->market_exchange : '' }}</span>
                                        <span class="badge {{ $asset->market_status === 'live' ? 'badge-emerald' : ($asset->market_status === 'error' ? 'badge-amber' : 'badge-slate') }}">{{ ucfirst($asset->market_status) }}</span>
                                    @else
                                        <span class="badge badge-slate">Manual valuation</span>
                                    @endif
                                </div>

                                <p class="mt-3 text-sm text-slate-500">Unit {{ rtrim(rtrim(number_format((float) $asset->units, 8, '.', ''), '0'), '.') ?: '0' }} - modal {{ \App\Support\FinancePresenter::money($asset->total_cost) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Nilai kini {{ \App\Support\FinancePresenter::money($asset->current_value) }} - harga sekarang {{ \App\Support\FinancePresenter::money($asset->current_price) }}</p>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-[1.5rem] bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Gain / loss</p>
                                        <p class="mt-2 text-lg font-bold {{ (float) $asset->market_change_amount >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ \App\Support\FinancePresenter::signedMoney((float) $asset->market_change_amount) }}</p>
                                        <p class="mt-1 text-sm {{ (float) $asset->market_change_percent >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format((float) $asset->market_change_percent, 2) }}%</p>
                                    </div>
                                    <div class="rounded-[1.5rem] bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Market update</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $asset->market_data_updated_at ? \App\Support\FinancePresenter::shortDate($asset->market_data_updated_at) : 'Belum ada data' }}</p>
                                        <p class="mt-1 text-sm text-slate-500">Provider {{ $asset->market_provider ?: 'manual' }}</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('investments.destroy', $asset) }}" onsubmit="return confirm('Hapus aset investasi ini?');" class="mt-4">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit">Hapus</x-secondary-button>
                                </form>
                            </div>

                            <div class="rounded-[1.75rem] border border-slate-200 bg-[var(--color-surface-muted)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-950">Chart market</p>
                                        <p class="text-sm text-slate-500">{{ $hasMarketTracking ? 'Pergerakan harga terbaru dari Yahoo Finance.' : 'Tambahkan market symbol untuk mengaktifkan chart live.' }}</p>
                                    </div>
                                    @if ($hasMarketTracking)
                                        <span class="badge badge-slate">{{ $asset->market_symbol }}</span>
                                    @endif
                                </div>

                                @if ($chartPoints)
                                    <div class="mt-4 rounded-[1.5rem] bg-white p-4">
                                        <svg viewBox="0 0 260 90" class="h-28 w-full">
                                            <polyline fill="none" stroke="#10b981" stroke-width="3" points="{{ $chartPoints }}" />
                                        </svg>
                                        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                            <span>{{ data_get($chartValues, '0.datetime', 'Awal') }}</span>
                                            <span>{{ data_get($chartValues, (count($chartValues) - 1).'.datetime', 'Terbaru') }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-4 rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">
                                        {{ $hasMarketTracking ? (data_get($assetMarket, 'message', 'Chart market belum tersedia saat ini.')) : 'Aset ini masih memakai valuasi manual karena belum memiliki market symbol.' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('investments.transactions.store', $asset) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'investment-account-'.$asset->id" value="Akun" />
                                    <select id="{{ 'investment-account-'.$asset->id }}" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :for="'investment-type-'.$asset->id" value="Jenis transaksi" />
                                    <select id="{{ 'investment-type-'.$asset->id }}" name="type" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="buy">Beli</option>
                                        <option value="sell">Jual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div>
                                    <x-input-label :for="'investment-transaction-date-'.$asset->id" value="Tanggal" />
                                    <x-text-input :id="'investment-transaction-date-'.$asset->id" name="transaction_date" type="date" :value="now()->toDateString()" />
                                </div>
                                <div>
                                    <x-input-label :for="'investment-transaction-units-'.$asset->id" value="Unit" />
                                    <x-text-input :id="'investment-transaction-units-'.$asset->id" name="units" type="number" step="0.00000001" min="0" />
                                </div>
                                <div>
                                    <x-input-label :for="'investment-transaction-price-'.$asset->id" value="Harga" />
                                    <x-text-input :id="'investment-transaction-price-'.$asset->id" name="price" type="number" step="0.01" min="0" />
                                </div>
                            </div>
                            <div>
                                <x-input-label :for="'investment-transaction-notes-'.$asset->id" value="Catatan" />
                                <x-text-input :id="'investment-transaction-notes-'.$asset->id" name="notes" type="text" :value="'Transaksi '.$asset->name" />
                            </div>
                            <x-primary-button>Simpan transaksi investasi</x-primary-button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-200 bg-white p-5 text-sm text-slate-500">Belum ada portofolio investasi.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
