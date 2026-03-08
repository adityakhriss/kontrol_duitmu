<x-app-layout>
    <x-slot name="title">Transaksi</x-slot>
    <x-slot name="heading">Pemasukan dan Pengeluaran</x-slot>
    <x-slot name="description">List transaksi dengan filter, kategori, akun, dan arah cashflow.</x-slot>

    <div class="space-y-6">
        <x-ui.panel title="Filter transaksi" description="Kontrol sederhana untuk periode, akun, dan kategori.">
            <div class="grid gap-4 lg:grid-cols-4">
                <select class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500"><option>Data terbaru</option></select>
                <select class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">@foreach ($accounts as $account)<option>{{ $account->name }}</option>@endforeach</select>
                <select class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">@foreach ($categories as $category)<option>{{ $category->name }}</option>@endforeach</select>
                <a href="{{ route('transactions.create') }}" class="inline-flex items-center justify-center rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">Tambah transaksi</a>
            </div>
        </x-ui.panel>

        <x-ui.panel title="Daftar transaksi" description="Versi mobile nanti dapat berubah jadi stacked cards.">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Akun</th>
                            <th>Kategori</th>
                            <th>Catatan</th>
                            <th>Nominal</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td>{{ \App\Support\FinancePresenter::shortDate($transaction->transaction_date) }}</td>
                                    <td><span class="badge {{ $transaction->type === 'income' ? 'badge-emerald' : 'badge-rose' }}">{{ $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span></td>
                                    <td>{{ $transaction->paymentAccount?->name ?? '-' }}</td>
                                    <td>{{ $transaction->category?->name ?? '-' }}</td>
                                    <td>{{ $transaction->notes ?? '-' }}</td>
                                    <td class="font-semibold {{ $transaction->type === 'income' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $transaction->type === 'income' ? '+' : '-' }}{{ \App\Support\FinancePresenter::money($transaction->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-slate-500">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $transactions->links() }}</div>
            </x-ui.panel>
        </div>
</x-app-layout>
