@props(['title' => null, 'description' => null, 'class' => ''])

<section {{ $attributes->class(['surface-panel p-5 sm:p-6', $class]) }}>
    @if ($title || $description)
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                @if ($title)
                    <h3 class="text-lg font-bold text-slate-950">{{ $title }}</h3>
                @endif
                @if ($description)
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                @endif
            </div>

            @isset($aside)
                {{ $aside }}
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
