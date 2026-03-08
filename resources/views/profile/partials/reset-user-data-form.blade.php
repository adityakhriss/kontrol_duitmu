<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Reset Data Finansial</h2>

        <p class="mt-1 text-sm text-gray-600">
            Hapus seluruh transaksi, tagihan, goal, hutang, investasi, histori mutasi, dan sinkronisasi kalender milik akun ini. Akun login tetap ada.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-data-reset')"
    >Reset semua data</x-danger-button>

    <x-modal name="confirm-user-data-reset" :show="$errors->userDataReset->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.reset-data') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Yakin ingin reset semua data finansial?</h2>

            <p class="mt-1 text-sm text-gray-600">
                Semua data finansial akan dihapus permanen dan saldo akun akan dikembalikan ke nol. Masukkan password untuk melanjutkan.
            </p>

            <div class="mt-6">
                <x-input-label for="reset_data_password" value="Password" class="sr-only" />
                <x-text-input id="reset_data_password" name="password" type="password" class="mt-1 block w-3/4" placeholder="Password" />
                <x-input-error :messages="$errors->userDataReset->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>

                <x-danger-button class="ms-3">Reset data</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
