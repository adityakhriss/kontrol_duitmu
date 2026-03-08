<x-app-layout>
    <x-slot name="title">Tambah Transaksi</x-slot>
    <x-slot name="heading">Form Transaksi Baru</x-slot>
    <x-slot name="description">Template frontend untuk pemasukan dan pengeluaran dengan pilihan akun serta kategori.</x-slot>

    <div class="grid gap-6 xl:grid-cols-[1fr_0.8fr]">
        <x-ui.panel title="Input transaksi" description="Field inti yang wajib ada sesuai PRD.">
            <form method="POST" action="{{ route('transactions.store') }}" class="grid gap-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="transaction-date" value="Tanggal" />
                        <x-text-input id="transaction-date" name="transaction_date" type="date" :value="old('transaction_date', now()->toDateString())" />
                    </div>
                    <div>
                        <x-input-label for="transaction-type" value="Jenis transaksi" />
                        <select id="transaction-type" name="type" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="account" value="Sumber saldo" />
                        <select id="account" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select>
                    </div>
                    <div>
                        <x-input-label for="category" value="Kategori" />
                        <select id="category" name="category_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach ($categories as $type => $items)
                                <optgroup label="{{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
                                    @foreach ($items as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <x-input-label for="nominal" value="Nominal" />
                    <x-text-input id="nominal" name="amount" type="number" step="0.01" :value="old('amount', '125000')" />
                </div>
                <div>
                    <x-input-label for="notes" value="Catatan" />
                    <textarea id="notes" name="notes" rows="4" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', 'Belanja bahan makan untuk dua hari.') }}</textarea>
                </div>
                <x-primary-button>Simpan transaksi</x-primary-button>
            </form>
        </x-ui.panel>

        <x-ui.panel title="Kategori default" description="Daftar kategori pengeluaran yang sudah ditentukan PRD.">
            <div class="flex flex-wrap gap-2">
                @foreach ($categories->flatten() as $category)
                    <span class="badge badge-slate">{{ $category->name }}</span>
                @endforeach
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
