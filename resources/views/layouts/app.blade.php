@php
    $pageTitle = trim((string) ($title ?? 'Kontrol Duitmu'));
    $pageHeading = trim((string) ($heading ?? $pageTitle));
    $pageDescription = trim((string) ($description ?? 'Pantau uang masuk, uang keluar, dan target finansial dalam satu workspace.'));
    $activeNavigation = trim((string) ($navigation ?? 'user'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $pageTitle }} - {{ config('app.name', 'Kontrol Duitmu') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body x-data="{ navOpen: false }" class="min-h-screen bg-[var(--color-bg)] text-slate-900">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute left-12 top-10 h-72 w-72 rounded-full bg-emerald-100/60 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-amber-100/40 blur-3xl"></div>
        </div>

        @include('layouts.navigation', ['navigation' => $activeNavigation])

        <div class="relative min-h-screen lg:pl-80">
            <header class="glass-topbar sticky top-0 z-30">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="navOpen = true" class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/70 bg-white/80 text-slate-700 shadow-soft lg:hidden">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-2xl font-bold text-slate-950 sm:text-3xl">{{ $pageHeading }}</h1>
                            <p class="mt-1 text-sm text-slate-500 sm:text-base">{{ $pageDescription }}</p>
                        </div>
                    </div>

                    <div class="hidden items-center gap-3 lg:flex">
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">{{ Auth::user()->name }}</p>
                        </div>

                        @if ($activeNavigation === 'admin')
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">User view</a>
                        @elseif (Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Admin view</a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Profil</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('status'))
                    <div class="mb-6">
                        <x-auth-session-status :status="session('status')" />
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
