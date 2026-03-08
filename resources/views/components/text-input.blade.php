@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-2xl border-slate-200 bg-white/90 px-4 py-3 text-slate-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500']) }}>
