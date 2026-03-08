<x-app-layout>
    <x-slot name="title">Transfer Saldo</x-slot>
    <x-slot name="heading">Transfer Antar Akun</x-slot>
    <x-slot name="description">Alur dummy untuk memindahkan dana antar tunai, bank, dan e-wallet.</x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-ui.panel title="Form transfer" description="Nominal harus lebih dari nol dan akun asal tidak boleh sama dengan akun tujuan.">
            <form method="POST" action="{{ route('transfers.store') }}" class="grid gap-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="source" value="Akun asal" />
                        <select id="source" name="from_payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="destination" value="Akun tujuan" />
                        <select id="destination" name="to_payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <x-input-label for="transfer-date" value="Tanggal transfer" />
                    <x-text-input id="transfer-date" name="transfer_date" type="date" :value="old('transfer_date', now()->toDateString())" />
                </div>
                <div>
                    <x-input-label for="amount" value="Nominal" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" :value="old('amount', '500000')" />
                </div>
                <div>
                    <x-input-label for="note" value="Catatan opsional" />
                    <textarea id="note" name="notes" rows="4" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', 'Top up saldo harian untuk transaksi transport dan makan.') }}</textarea>
                </div>
                <x-primary-button>Simpan transfer</x-primary-button>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Aturan transfer" description="Business rules utama dari PRD.">
            <div class="space-y-3">
                @foreach (['Transfer tidak dihitung sebagai pemasukan maupun pengeluaran.', 'Saldo asal wajib cukup sebelum transaksi disimpan.', 'Setiap perpindahan harus masuk histori mutasi dua sisi: keluar dan masuk.', 'Akun asal dan tujuan tidak boleh sama untuk mencegah mutasi kosong.'] as $rule)
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm leading-7 text-slate-600">{{ $rule }}</div>
                @endforeach

                @if ($recentTransfers->isNotEmpty())
                    <div class="rounded-[1.5rem] bg-slate-50 p-4">
                        <p class="mb-3 font-semibold text-slate-900">Transfer terbaru</p>
                        <div class="space-y-2 text-sm text-slate-600">
                            @foreach ($recentTransfers as $transfer)
                                <div>{{ $transfer->fromAccount?->name }} -> {{ $transfer->toAccount?->name }} - {{ \App\Support\FinancePresenter::money($transfer->amount) }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
