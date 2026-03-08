@props(['value' => 0, 'tone' => 'emerald'])

@php
    $toneClasses = [
        'emerald' => 'from-emerald-500 to-teal-400',
        'amber' => 'from-amber-500 to-yellow-400',
        'rose' => 'from-rose-500 to-orange-400',
        'slate' => 'from-slate-500 to-slate-400',
    ];
    $progress = max(0, min(100, (int) $value));
@endphp

<div class="h-3 overflow-hidden rounded-full bg-slate-100">
    <div class="h-full rounded-full bg-gradient-to-r {{ $toneClasses[$tone] ?? $toneClasses['emerald'] }}" style="width: {{ $progress }}%"></div>
</div>
