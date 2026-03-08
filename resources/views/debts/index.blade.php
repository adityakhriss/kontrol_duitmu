<x-app-layout>
    <x-slot name="title">Hutang</x-slot>
    <x-slot name="heading">Manajemen Hutang</x-slot>
    <x-slot name="description">Tambah pinjaman, catat pembayaran, dan pantau sisa kewajiban dari data nyata.</x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Tambah hutang" description="Setup hutang, cicilan, atau kartu kredit baru.">
                <form method="POST" action="{{ route('debts.store') }}" class="grid gap-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="debt-name" value="Nama hutang" />
                            <x-text-input id="debt-name" name="name" type="text" :value="old('name')" />
                        </div>
                        <div>
                            <x-input-label for="debt-lender" value="Pemberi pinjaman" />
                            <x-text-input id="debt-lender" name="lender_name" type="text" :value="old('lender_name')" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="debt-total" value="Total hutang" />
                            <x-text-input id="debt-total" name="total_amount" type="number" step="0.01" min="0" :value="old('total_amount')" />
                        </div>
                        <div>
                            <x-input-label for="debt-monthly" value="Cicilan per bulan" />
                            <x-text-input id="debt-monthly" name="monthly_payment" type="number" step="0.01" min="0" :value="old('monthly_payment')" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="debt-interest" value="Bunga %" />
                            <x-text-input id="debt-interest" name="interest_rate" type="number" step="0.01" min="0" :value="old('interest_rate')" />
                        </div>
                        <div>
                            <x-input-label for="debt-start" value="Tanggal mulai" />
                            <x-text-input id="debt-start" name="start_date" type="date" :value="old('start_date')" />
                        </div>
                        <div>
                            <x-input-label for="debt-due" value="Jatuh tempo" />
                            <x-text-input id="debt-due" name="due_date" type="date" :value="old('due_date')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="debt-notes" value="Catatan" />
                        <textarea id="debt-notes" name="notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                    <x-primary-button>Tambah hutang</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Insight hutang" description="Ringkasan kewajiban yang masih aktif.">
                <div class="grid gap-3">
                    <div class="rounded-[1.5rem] bg-amber-50 p-4 text-amber-900">Total cicilan aktif {{ \App\Support\FinancePresenter::money($debts->where('status', 'active')->sum('monthly_payment')) }} untuk periode berjalan.</div>
                    <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Pembayaran hutang akan otomatis tercatat sebagai pengeluaran dan mengurangi saldo akun pembayaran.</div>
                    <div class="rounded-[1.5rem] bg-white p-4 text-slate-600">Sisa total hutang saat ini {{ \App\Support\FinancePresenter::money($debts->sum('remaining_amount')) }}.</div>
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Hutang aktif" description="Setiap pembayaran akan menurunkan sisa hutang dan tersimpan di histori.">
            <div class="space-y-4">
                @forelse ($debts as $debt)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-950">{{ $debt->name }}</p>
                                    <span class="badge {{ $debt->status === 'paid' ? 'badge-emerald' : 'badge-amber' }}">{{ $debt->status === 'paid' ? 'Lunas' : 'Aktif' }}</span>
                                </div>
                                <p class="mt-2 text-sm text-slate-500">{{ $debt->lender_name ?: 'Tanpa pemberi pinjaman' }} - sisa {{ \App\Support\FinancePresenter::money($debt->remaining_amount) }} dari {{ \App\Support\FinancePresenter::money($debt->total_amount) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Cicilan {{ \App\Support\FinancePresenter::money($debt->monthly_payment) }} - jatuh tempo {{ \App\Support\FinancePresenter::shortDate($debt->due_date) }}</p>
                            </div>
                            <form method="POST" action="{{ route('debts.destroy', $debt) }}" onsubmit="return confirm('Hapus data hutang ini?');">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button type="submit">Hapus</x-secondary-button>
                            </form>
                        </div>

                        @if ($debt->status !== 'paid')
                            <form method="POST" action="{{ route('debts.payments.store', $debt) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 lg:grid-cols-[1fr_0.7fr_0.7fr_auto] lg:items-end">
                                @csrf
                                <div>
                                    <x-input-label :for="'debt-account-'.$debt->id" value="Bayar dari akun" />
                                    <select id="{{ 'debt-account-'.$debt->id }}" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :for="'debt-paid-on-'.$debt->id" value="Tanggal bayar" />
                                    <x-text-input :id="'debt-paid-on-'.$debt->id" name="paid_on" type="date" :value="now()->toDateString()" />
                                </div>
                                <div>
                                    <x-input-label :for="'debt-amount-'.$debt->id" value="Nominal" />
                                    <x-text-input :id="'debt-amount-'.$debt->id" name="amount" type="number" step="0.01" min="0" :value="(float) min($debt->monthly_payment, $debt->remaining_amount)" />
                                </div>
                                <x-primary-button>Catat pembayaran</x-primary-button>
                                <div class="lg:col-span-4">
                                    <x-input-label :for="'debt-notes-'.$debt->id" value="Catatan" />
                                    <x-text-input :id="'debt-notes-'.$debt->id" name="notes" type="text" :value="'Bayar hutang '.$debt->name" />
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada data hutang.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
