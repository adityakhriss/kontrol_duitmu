<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Kontrol Duitmu') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-bg)] text-slate-900 antialiased">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute left-0 top-0 h-[26rem] w-[26rem] rounded-full bg-emerald-200/40 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-[28rem] w-[28rem] rounded-full bg-amber-100/45 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-6 lg:px-8">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-sm font-bold text-white shadow-soft">KD</span>
                    <span>
                        <span class="block text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Finance workspace</span>
                        <span class="block font-display text-xl text-slate-950">Kontrol Duitmu</span>
                    </span>
                </a>

                <a href="{{ url('/') }}" class="text-sm font-semibold text-slate-600 transition hover:text-slate-950">Kembali ke landing</a>
            </div>

            <div class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[0.95fr_0.8fr] lg:py-16">
                <div class="hidden space-y-6 lg:block">
                    <span class="badge badge-emerald">Manual tracking, clear visibility</span>
                    <h1 class="max-w-xl font-display text-5xl leading-tight text-slate-950">Bangun kebiasaan finansial yang tertata tanpa dashboard yang melelahkan.</h1>
                    <p class="max-w-xl text-lg leading-8 text-slate-600">Setiap pemasukan, pengeluaran, transfer internal, dan target tabungan tersimpan dalam layout yang ringkas dan nyaman di desktop maupun mobile.</p>
                    <div class="grid max-w-xl gap-4 sm:grid-cols-2">
                        <div class="surface-panel p-5">
                            <p class="text-sm text-slate-500">Cashflow fokus</p>
                            <p class="mt-2 text-2xl font-extrabold text-slate-950">Realtime overview</p>
                        </div>
                        <div class="surface-panel p-5">
                            <p class="text-sm text-slate-500">Planning fokus</p>
                            <p class="mt-2 text-2xl font-extrabold text-slate-950">Bills + goals + insight</p>
                        </div>
                    </div>
                </div>

                <div class="auth-card max-w-xl justify-self-center">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
