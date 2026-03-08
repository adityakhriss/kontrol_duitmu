<x-app-layout>
    <x-slot name="title">Tagihan</x-slot>
    <x-slot name="heading">Tagihan Bulanan</x-slot>
    <x-slot name="description">Tambah, hapus, dan bayar tagihan langsung dari data riil akunmu.</x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Tambah tagihan" description="Siapkan tagihan rutin maupun satu kali bayar.">
                <form method="POST" action="{{ route('bills.store') }}" class="grid gap-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="bill-name" value="Nama tagihan" />
                            <x-text-input id="bill-name" name="name" type="text" :value="old('name')" />
                        </div>
                        <div>
                            <x-input-label for="bill-category" value="Kategori" />
                            <x-text-input id="bill-category" name="category_name" type="text" :value="old('category_name', 'Tagihan')" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <x-input-label for="bill-amount" value="Nominal" />
                            <x-text-input id="bill-amount" name="amount" type="number" step="0.01" min="0" :value="old('amount')" />
                        </div>
                        <div>
                            <x-input-label for="bill-due-date" value="Jatuh tempo" />
                            <x-text-input id="bill-due-date" name="due_date" type="date" :value="old('due_date', now()->toDateString())" />
                        </div>
                        <div>
                            <x-input-label for="bill-account" value="Akun bayar default" />
                            <select id="bill-account" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Pilih nanti</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected((string) old('payment_account_id') === (string) $account->id)>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-[auto_1fr] sm:items-center">
                        <label class="inline-flex items-center gap-3 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_recurring" value="1" @checked(old('is_recurring')) class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            Tagihan berulang
                        </label>
                        <select name="recurring_period" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('recurring_period', 'monthly') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="bill-notes" value="Catatan" />
                        <textarea id="bill-notes" name="notes" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                    <x-primary-button>Tambah tagihan</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Histori pembayaran" description="Tagihan yang dibayar otomatis tercatat sebagai pengeluaran.">
                <div class="space-y-3">
                    @forelse ($paymentHistory as $payment)
                        <div class="rounded-[1.5rem] bg-white p-4">
                            <p class="font-semibold text-slate-950">{{ $payment->bill?->name }} - {{ \App\Support\FinancePresenter::money($payment->amount) }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ \App\Support\FinancePresenter::shortDate($payment->paid_on) }} - {{ $payment->paymentAccount?->name ?? '-' }}</p>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada histori pembayaran tagihan.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Daftar tagihan" description="Kelola daftar kewajiban dan lakukan pembayaran saat diperlukan.">
            <div class="space-y-4">
                @forelse ($bills as $bill)
                    <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-semibold text-slate-950">{{ $bill->name }}</p>
                                    <span class="badge {{ $bill->status === 'paid' ? 'badge-emerald' : 'badge-amber' }}">{{ $bill->status === 'paid' ? 'Sudah dibayar' : 'Belum dibayar' }}</span>
                                    @if ($bill->is_recurring)
                                        <span class="badge badge-slate">{{ ucfirst($bill->recurring_period ?? 'monthly') }}</span>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm text-slate-500">{{ $bill->category_name }} - {{ \App\Support\FinancePresenter::money($bill->amount) }} - jatuh tempo {{ \App\Support\FinancePresenter::shortDate($bill->due_date) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Akun default: {{ $bill->paymentAccount?->name ?? 'Belum dipilih' }}</p>
                            </div>

                            <form method="POST" action="{{ route('bills.destroy', $bill) }}" onsubmit="return confirm('Hapus tagihan ini?');">
                                @csrf
                                @method('DELETE')
                                <x-secondary-button type="submit">Hapus</x-secondary-button>
                            </form>
                        </div>

                        @if ($bill->status !== 'paid' || $bill->is_recurring)
                            <form method="POST" action="{{ route('bills.payments.store', $bill) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4 lg:grid-cols-[1fr_0.8fr_0.9fr_auto] lg:items-end">
                                @csrf
                                <input type="hidden" name="amount" value="{{ $bill->amount }}">
                                <div>
                                    <x-input-label :for="'bill-account-'.$bill->id" value="Bayar dari akun" />
                                    <select id="{{ 'bill-account-'.$bill->id }}" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}" @selected((string) old('payment_account_id', $bill->payment_account_id) === (string) $account->id)>{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :for="'bill-paid-on-'.$bill->id" value="Tanggal bayar" />
                                    <x-text-input :id="'bill-paid-on-'.$bill->id" name="paid_on" type="date" :value="old('paid_on', now()->toDateString())" />
                                </div>
                                <div>
                                    <x-input-label :for="'bill-payment-notes-'.$bill->id" value="Catatan" />
                                    <x-text-input :id="'bill-payment-notes-'.$bill->id" name="notes" type="text" :value="old('notes', 'Bayar '.$bill->name)" />
                                </div>
                                <x-primary-button>Bayar {{ \App\Support\FinancePresenter::money($bill->amount) }}</x-primary-button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">Belum ada tagihan tersimpan.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
