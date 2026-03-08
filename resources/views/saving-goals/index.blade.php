<x-app-layout>
    <x-slot name="title">Goal Tabungan</x-slot>
    <x-slot name="heading">Goal dan Progress Tabungan</x-slot>
    <x-slot name="description">Buat target baru lalu setor atau tarik dana dari akun nyata.</x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-4 lg:grid-cols-3">
            <x-ui.stat-card label="Goal aktif" :value="$goals->where('status', 'active')->count().' target'" trend="Fokus target berjalan" tone="emerald" />
            <x-ui.stat-card label="Saldo terkumpul" :value="\App\Support\FinancePresenter::money($goals->sum('current_amount'))" trend="Akumulasi seluruh goal" tone="amber" />
            <x-ui.stat-card label="Target tercepat" :value="$goals->sortByDesc(fn ($goal) => $goal->target_amount > 0 ? $goal->current_amount / $goal->target_amount : 0)->first()?->name ?? '-'" trend="Progress tertinggi saat ini" tone="slate" />
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <x-ui.panel title="Buat goal baru" description="Tentukan target, tenggat, dan konteks target finansial.">
                <form method="POST" action="{{ route('saving-goals.store') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <x-input-label for="goal-name" value="Nama goal" />
                        <x-text-input id="goal-name" name="name" type="text" :value="old('name')" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="goal-target" value="Target dana" />
                            <x-text-input id="goal-target" name="target_amount" type="number" step="0.01" min="0" :value="old('target_amount')" />
                        </div>
                        <div>
                            <x-input-label for="goal-date" value="Target tercapai" />
                            <x-text-input id="goal-date" name="target_date" type="date" :value="old('target_date')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="goal-description" value="Deskripsi" />
                        <textarea id="goal-description" name="description" rows="3" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                    </div>
                    <x-primary-button>Buat goal</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Riwayat mutasi goal" description="Semua setoran dan penarikan dana goal tercatat di sini.">
                <div class="space-y-3">
                    @forelse ($goalHistory as $entry)
                        <div class="rounded-[1.5rem] bg-white p-4">
                            <p class="font-semibold text-slate-950">{{ $entry->savingGoal?->name ?? 'Goal' }} - {{ $entry->entry_type === 'deposit' ? 'Setor' : 'Tarik' }} {{ \App\Support\FinancePresenter::money($entry->amount) }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ \App\Support\FinancePresenter::shortDate($entry->entry_date) }} - {{ $entry->paymentAccount?->name ?? '-' }}</p>
                        </div>
                    @empty
                        <div class="rounded-[1.5rem] bg-white p-4 text-sm text-slate-500">Belum ada mutasi goal tabungan.</div>
                    @endforelse
                </div>
            </x-ui.panel>
        </section>

        <x-ui.panel title="Daftar goal" description="Setor dan tarik dana sekarang langsung terhubung ke saldo akun.">
            <div class="grid gap-4 xl:grid-cols-2">
                @forelse ($goals as $goal)
                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-lg font-bold text-slate-950">{{ $goal->name }}</p>
                                <p class="text-sm text-slate-500">{{ \App\Support\FinancePresenter::money($goal->current_amount) }} / {{ \App\Support\FinancePresenter::money($goal->target_amount) }}</p>
                                <p class="mt-1 text-sm text-slate-500">Target {{ \App\Support\FinancePresenter::shortDate($goal->target_date) }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="badge {{ $goal->status === 'completed' ? 'badge-emerald' : 'badge-slate' }}">{{ $goal->status === 'completed' ? 'Selesai' : 'Aktif' }}</span>
                                <form method="POST" action="{{ route('saving-goals.destroy', $goal) }}" onsubmit="return confirm('Hapus goal ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit">Hapus</x-secondary-button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-4"><x-ui.progress-bar :value="(int) round(($goal->current_amount / max(1, $goal->target_amount)) * 100)" /></div>

                        <form method="POST" action="{{ route('saving-goals.entries.store', $goal) }}" class="mt-5 grid gap-3 border-t border-slate-100 pt-4">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'goal-account-'.$goal->id" value="Akun" />
                                    <select id="{{ 'goal-account-'.$goal->id }}" name="payment_account_id" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label :for="'goal-entry-type-'.$goal->id" value="Jenis mutasi" />
                                    <select id="{{ 'goal-entry-type-'.$goal->id }}" name="entry_type" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="deposit">Setor dana</option>
                                        <option value="withdraw">Tarik dana</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <x-input-label :for="'goal-amount-'.$goal->id" value="Nominal" />
                                    <x-text-input :id="'goal-amount-'.$goal->id" name="amount" type="number" step="0.01" min="0" />
                                </div>
                                <div>
                                    <x-input-label :for="'goal-date-entry-'.$goal->id" value="Tanggal" />
                                    <x-text-input :id="'goal-date-entry-'.$goal->id" name="entry_date" type="date" :value="now()->toDateString()" />
                                </div>
                            </div>
                            <div>
                                <x-input-label :for="'goal-notes-'.$goal->id" value="Catatan" />
                                <x-text-input :id="'goal-notes-'.$goal->id" name="notes" type="text" :value="'Mutasi goal '.$goal->name" />
                            </div>
                            <x-primary-button>Simpan mutasi goal</x-primary-button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-[1.75rem] border border-dashed border-slate-200 bg-white p-5 text-sm text-slate-500">Belum ada goal tabungan.</div>
                @endforelse
            </div>
        </x-ui.panel>
    </div>
</x-app-layout>
