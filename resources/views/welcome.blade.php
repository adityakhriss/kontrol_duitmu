<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kontrol Duitmu</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fraunces:600,700|manrope:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--color-bg)] text-slate-900">
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute -top-32 left-1/2 h-[28rem] w-[28rem] -translate-x-1/2 rounded-full bg-emerald-200/45 blur-3xl"></div>
            <div class="absolute right-0 top-48 h-80 w-80 rounded-full bg-amber-200/40 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-96 w-96 rounded-full bg-teal-200/30 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-6 py-6 lg:px-8">
            <main class="grid flex-1 items-center gap-10 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:py-16">
                <section class="space-y-8">
                    <div class="space-y-5">
                        <h1 class="max-w-3xl font-display text-5xl leading-tight text-slate-950 sm:text-6xl">Rapikan uang harian, tagihan, dan target finansial dalam satu ruang kerja yang enak dipakai.</h1>
                        <p class="max-w-2xl text-lg leading-8 text-slate-600">Kontrol Duitmu membantu kamu mengelola saldo, transaksi, tagihan, goal tabungan, hutang, dan investasi dalam satu aplikasi yang rapi, jelas, dan nyaman dipakai setiap hari.</p>
                    </div>

                    <div class="flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-slate-950 px-6 py-3 text-sm font-semibold text-white shadow-soft transition hover:-translate-y-0.5 hover:bg-emerald-700">Buat akun gratis</a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-950 hover:text-slate-950">Masuk ke akun</a>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="surface-panel p-5">
                            <p class="text-sm text-slate-500">Akun utama</p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-950">3</p>
                            <p class="mt-2 text-sm text-slate-600">Tunai, Bank, dan E-wallet dipisah jelas.</p>
                        </div>
                        <div class="surface-panel p-5">
                            <p class="text-sm text-slate-500">Modul MVP</p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-950">5+</p>
                            <p class="mt-2 text-sm text-slate-600">Saldo, transaksi, tagihan, goal, dan dashboard.</p>
                        </div>
                        <div class="surface-panel p-5">
                            <p class="text-sm text-slate-500">Integrasi roadmap</p>
                            <p class="mt-2 text-3xl font-extrabold text-slate-950">2</p>
                            <p class="mt-2 text-sm text-slate-600">RSS berita IDX, Yahoo Finance, dan Google Calendar.</p>
                        </div>
                    </div>
                </section>

                <section class="surface-panel overflow-hidden p-5 sm:p-7">
                    <div class="grid gap-5">
                        <div class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-soft">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm text-white/70">Aset likuid</p>
                                    <p class="mt-3 text-4xl font-extrabold tracking-tight">Rp18.450.000</p>
                                </div>
                                <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-emerald-200">+12.4% bulan ini</span>
                            </div>
                            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl bg-white/10 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/50">Tunai</p>
                                    <p class="mt-2 text-lg font-semibold">Rp1.250.000</p>
                                </div>
                                <div class="rounded-2xl bg-white/10 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/50">Bank</p>
                                    <p class="mt-2 text-lg font-semibold">Rp13.800.000</p>
                                </div>
                                <div class="rounded-2xl bg-white/10 p-4">
                                    <p class="text-xs uppercase tracking-[0.2em] text-white/50">E-wallet</p>
                                    <p class="mt-2 text-lg font-semibold">Rp3.400.000</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                            <div class="rounded-[1.75rem] border border-slate-200 bg-[var(--color-surface-muted)] p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-slate-500">Tagihan terdekat</p>
                                        <h2 class="mt-1 text-xl font-bold text-slate-950">3 pengingat minggu ini</h2>
                                    </div>
                                    <span class="badge badge-amber">Kalender sink ready</span>
                                </div>
                                <div class="mt-5 space-y-3">
                                    <div class="rounded-2xl bg-white p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-slate-900">Internet rumah</p>
                                            <p class="text-sm font-semibold text-amber-700">11 Mar</p>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">Rp385.000 - bayar via bank</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-slate-900">Cicilan laptop</p>
                                            <p class="text-sm font-semibold text-amber-700">14 Mar</p>
                                        </div>
                                        <p class="mt-1 text-sm text-slate-500">Rp1.250.000 - recurring aktif</p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                                <p class="text-sm text-slate-500">Goal tabungan</p>
                                <h2 class="mt-1 text-xl font-bold text-slate-950">Dana darurat</h2>
                                <div class="mt-5 h-3 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full w-[68%] rounded-full bg-gradient-to-r from-emerald-500 to-teal-400"></div>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Terkumpul Rp13.600.000</span>
                                    <span class="font-semibold text-slate-900">68%</span>
                                </div>
                                <div class="mt-5 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-900">Alokasi bulanan konsisten. Estimasi tercapai dalam 4 bulan lagi.</div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
