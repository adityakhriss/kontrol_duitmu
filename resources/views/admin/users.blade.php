<x-app-layout>
    <x-slot name="title">Users</x-slot>
    <x-slot name="heading">Manajemen User</x-slot>
    <x-slot name="description">CRUD user untuk admin, termasuk role, status aktif, dan reset password manual.</x-slot>
    <x-slot name="navigation">admin</x-slot>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <x-ui.panel title="Tambah user" description="Buat akun user atau admin baru dari panel admin.">
                <form method="POST" action="{{ route('admin.users.store') }}" class="grid gap-4">
                    @csrf
                    <div>
                        <x-input-label for="admin-user-name" value="Nama" />
                        <x-text-input id="admin-user-name" name="name" type="text" :value="old('name')" />
                    </div>
                    <div>
                        <x-input-label for="admin-user-email" value="Email" />
                        <x-text-input id="admin-user-email" name="email" type="email" :value="old('email')" />
                    </div>
                    <div>
                        <x-input-label for="admin-user-password" value="Password" />
                        <x-text-input id="admin-user-password" name="password" type="password" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="admin-user-role" value="Role" />
                            <select id="admin-user-role" name="role" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="user" @selected(old('role') === 'user')>User</option>
                                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="admin-user-active" value="Status" />
                            <select id="admin-user-active" name="is_active" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="1" @selected(old('is_active', '1') === '1')>Aktif</option>
                                <option value="0" @selected(old('is_active') === '0')>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <x-primary-button>Tambah user</x-primary-button>
                </form>
            </x-ui.panel>

            <x-ui.panel title="Daftar user" description="Tabel user dengan aksi edit dan hapus.">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        @foreach ($users as $user)
                            <tbody x-data="{ open: false }" class="divide-y divide-slate-100">
                                <tr>
                                    <td class="font-semibold text-slate-950">{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge {{ $user->role === 'admin' ? 'badge-amber' : 'badge-slate' }}">{{ ucfirst($user->role) }}</span></td>
                                    <td><span class="badge {{ $user->is_active ? 'badge-emerald' : 'badge-amber' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td>{{ \App\Support\FinancePresenter::shortDate($user->created_at) }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="open = ! open" class="rounded-full border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Edit</button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-full border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:border-rose-600 hover:text-rose-800">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr x-show="open" x-cloak>
                                    <td colspan="6" class="bg-slate-50/80">
                                        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 p-4 lg:grid-cols-2">
                                            @csrf
                                            @method('PATCH')

                                            <div>
                                                <x-input-label :for="'user-name-'.$user->id" value="Nama" />
                                                <x-text-input :id="'user-name-'.$user->id" name="name" type="text" :value="$user->name" />
                                            </div>
                                            <div>
                                                <x-input-label :for="'user-email-'.$user->id" value="Email" />
                                                <x-text-input :id="'user-email-'.$user->id" name="email" type="email" :value="$user->email" />
                                            </div>
                                            <div>
                                                <x-input-label :for="'user-role-'.$user->id" value="Role" />
                                                <select id="{{ 'user-role-'.$user->id }}" name="role" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                                    <option value="user" @selected($user->role === 'user')>User</option>
                                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                                </select>
                                            </div>
                                            <div>
                                                <x-input-label :for="'user-active-'.$user->id" value="Status" />
                                                <select id="{{ 'user-active-'.$user->id }}" name="is_active" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-emerald-500 focus:ring-emerald-500">
                                                    <option value="1" @selected($user->is_active)>Aktif</option>
                                                    <option value="0" @selected(! $user->is_active)>Nonaktif</option>
                                                </select>
                                            </div>
                                            <div class="lg:col-span-2">
                                                <x-input-label :for="'user-password-'.$user->id" value="Password baru (opsional)" />
                                                <x-text-input :id="'user-password-'.$user->id" name="password" type="password" />
                                            </div>
                                            <div class="lg:col-span-2 flex justify-end">
                                                <x-primary-button>Simpan perubahan</x-primary-button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    </table>
                </div>
                <div class="mt-4">{{ $users->links() }}</div>
            </x-ui.panel>
        </section>
    </div>
</x-app-layout>
