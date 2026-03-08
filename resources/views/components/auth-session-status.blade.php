@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700']) }}>
        {{ $status }}
    </div>
@endif
