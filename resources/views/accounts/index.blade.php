<x-app-layout>
    <x-slot name="title">Saldo</x-slot>
    <x-slot name="heading">Akun Saldo</x-slot>
    <x-slot name="description">Pisahkan sumber dana utama dan pantau mutasi tiap akun dengan jelas.</x-slot>

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-3">
            @foreach ($accounts as $account)
                <x-ui.stat-card :label="$account->name" :value="\App\Support\FinancePresenter::money($account->balance)" :trend="ucfirst(str_replace('_', ' ', $account->type)).' account'" :tone="$loop->first ? 'amber' : ($loop->iteration === 2 ? 'emerald' : 'slate')" />
            @endforeach
        </section>

        <section class="grid gap-4 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Aksi saldo" description="Alur paling umum untuk pengelolaan saldo.">
                <div class="grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('accounts.transfer') }}" class="rounded-[1.5rem] bg-slate-950 p-5 text-white">
                        <p class="font-semibold">Transfer internal</p>
                        <p class="mt-2 text-sm text-white/70">Pindahkan saldo antar akun tanpa memengaruhi income/expense.</p>
                    </a>
                    <div class="rounded-[1.5rem] bg-white p-5">
                        <p class="font-semibold text-slate-950">Pencatatan transaksi</p>
                        <p class="mt-2 text-sm text-slate-500">Gunakan tambah transaksi untuk memperbarui saldo masuk dan keluar secara rapi.</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-white p-5">
                        <p class="font-semibold text-slate-950">Histori mutasi</p>
                        <p class="mt-2 text-sm text-slate-500">Lacak asal perubahan saldo dari transaksi dan transfer.</p>
                    </div>
                        <div class="rounded-[1.5rem] bg-white p-5">
                            <p class="font-semibold text-slate-950">Total gabungan</p>
                            <p class="mt-2 text-sm text-slate-500">Pantau likuiditas tanpa kehilangan detail per akun. Saat ini {{ $totalBalance }}.</p>
                        </div>
                    </div>
                </x-ui.panel>

            <x-ui.panel title="Mutasi terakhir" description="Preview data list yang nanti dihubungkan ke backend.">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Akun</th>
                                <th>Aktivitas</th>
                                <th>Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentMutations as $mutation)
                                <tr>
                                    <td>{{ \App\Support\FinancePresenter::shortDate($mutation->mutation_date) }}</td>
                                    <td>{{ $mutation->paymentAccount?->name ?? '-' }}</td>
                                    <td>{{ $mutation->description ?? ucfirst($mutation->mutation_type) }}</td>
                                    <td class="font-semibold {{ $mutation->direction === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $mutation->direction === 'credit' ? '+' : '-' }}{{ \App\Support\FinancePresenter::money($mutation->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-500">Belum ada mutasi akun.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
