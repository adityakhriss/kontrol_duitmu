<x-app-layout>
    <x-slot name="title">Investasi</x-slot>
    <x-slot name="heading">Portofolio Investasi</x-slot>
    <x-slot name="description">Tambah aset, catat beli-jual, dan pantau nilai portofolio dari transaksi real.</x-slot>

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
            <x-ui.panel title="Tambah aset investasi" description="Buat aset baru beserta pembelian awalnya.">
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
                        <div>
                            <x-input-label for="investment-current-price" value="Harga saat ini" />
                            <x-text-input id="investment-current-price" name="current_price" type="number" step="0.01" min="0" :value="old('current_price')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="investment-notes" value="Catatan" />
                        <textarea id="investment-notes" name="notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                    <x-primary-button>Tambah aset</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Ringkasan portofolio" description="Valuasi dihitung dari unit yang masih dimiliki dan harga saat ini.">
                <div class="space-y-3">
                    @forelse ($investments as $asset)
                        <div class="rounded-[1.5rem] bg-white p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-950">{{ $asset->name }}</p>
                                    <p class="text-sm text-slate-500">{{ $asset->type }} - {{ $asset->platform ?: 'Tanpa platform' }}</p>
                                </div>
                                <span class="badge badge-emerald">{{ \App\Support\FinancePresenter::money($asset->current_value) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada aset investasi.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Aset investasi" description="Setiap transaksi beli dan jual akan mengubah saldo akun dan valuasi portofolio.">
            <div class="grid gap-4 xl:grid-cols-2">
                @forelse ($investments as $asset)
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="font-semibold text-slate-950">{{ $asset->name }}</p>
                                <p class="text-sm text-slate-500">{{ $asset->type }}{{ $asset->ticker ? ' - '.$asset->ticker : '' }}</p>
                                <p class="mt-2 text-sm text-slate-500">Unit {{ rtrim(rtrim(number_format((float) $asset->units, 8, '.', ''), '0'), '.') ?: '0' }} - modal {{ \App\Support\FinancePresenter::money($asset->total_cost) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Nilai kini {{ \App\Support\FinancePresenter::money($asset->current_value) }} - harga sekarang {{ \App\Support\FinancePresenter::money($asset->current_price) }}</p>
                            </div>
                            <form method="POST" action="{{ route('investments.destroy', $asset) }}" onsubmit="return confirm('Hapus aset investasi ini?');">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button type="submit">Hapus</x-secondary-button>
                            </form>
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
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'investment-current-price-update-'.$asset->id" value="Update harga saat ini" />
                                    <x-text-input :id="'investment-current-price-update-'.$asset->id" name="current_price" type="number" step="0.01" min="0" :value="(float) $asset->current_price" />
                                </div>
                                <div>
                                    <x-input-label :for="'investment-transaction-notes-'.$asset->id" value="Catatan" />
                                    <x-text-input :id="'investment-transaction-notes-'.$asset->id" name="notes" type="text" :value="'Transaksi '.$asset->name" />
                                </div>
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
