<x-app-layout>
    <x-slot name="title">Profil</x-slot>
    <x-slot name="heading">Pengaturan Profil</x-slot>
    <x-slot name="description">Kelola informasi akun, keamanan login, dan preferensi dasar workspace.</x-slot>

    <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
        <x-ui.panel title="Ringkasan akun" description="Informasi singkat untuk identitas workspace.">
            <div class="rounded-[1.75rem] bg-slate-950 p-5 text-white">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-[1.5rem] bg-white/10 text-xl font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div>
                        <p class="text-xl font-bold">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-white/65">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-7 text-white/70">Halaman ini tetap dipertahankan simpel agar nanti mudah ditambah preferensi notifikasi, Google Calendar, dan API connection status.</p>
            </div>
        </x-ui.panel>

        <div class="space-y-6">
            <x-ui.panel title="Informasi profil" description="Perbarui nama dan email utama akun.">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-ui.panel>

            <x-ui.panel title="Keamanan akun" description="Ganti password untuk menjaga akses tetap aman.">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-ui.panel>

            <x-ui.panel title="Reset data" description="Kosongkan seluruh data finansial tanpa menghapus akun login.">
                <div class="max-w-2xl">
                    @include('profile.partials.reset-user-data-form')
                </div>
            </x-ui.panel>

            <x-ui.panel title="Hapus akun" description="Tindakan permanen bila akun tidak lagi digunakan.">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-ui.panel>
        </div>
    </div>
</x-app-layout>
