<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users', [
            'users' => User::query()->latest()->paginate(20),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => $request->string('role')->toString(),
            'is_active' => $request->boolean('is_active'),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users')->with('status', 'User berhasil ditambahkan.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id && ! $request->boolean('is_active')) {
            return redirect()->route('admin.users')->withErrors([
                'admin_user' => 'Admin tidak bisa menonaktifkan akunnya sendiri.',
            ]);
        }

        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => $request->string('role')->toString(),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->string('password')->toString());
        }

        $user->update($payload);

        return redirect()->route('admin.users')->with('status', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) request()->user()->id === (int) $user->id) {
            return redirect()->route('admin.users')->withErrors([
                'admin_user' => 'Admin tidak bisa menghapus akunnya sendiri.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User berhasil dihapus.');
    }
}
