@props([
    'label',
    'value',
    'trend' => null,
    'tone' => 'slate',
])

@php
    $toneClasses = [
        'emerald' => 'from-emerald-500/15 to-teal-400/10 border-emerald-100',
        'rose' => 'from-rose-500/15 to-orange-300/10 border-rose-100',
        'amber' => 'from-amber-400/15 to-yellow-300/10 border-amber-100',
        'slate' => 'from-slate-200/20 to-white border-slate-200',
    ];
@endphp

<div class="rounded-[1.75rem] border bg-gradient-to-br p-5 {{ $toneClasses[$tone] ?? $toneClasses['slate'] }}">
    <p class="text-sm text-slate-500">{{ $label }}</p>
    <p class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">{{ $value }}</p>
    @if ($trend)
        <p class="mt-3 text-sm font-semibold text-slate-600">{{ $trend }}</p>
    @endif
</div>
