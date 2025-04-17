@props(['active'])

@php
$classes = ($active ?? false)
            ? 'relative h-9 inline-flex gap-2 items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-600 hover:text-stone-800 hover:bg-stone-200 focus:outline-none focus:bg-stone-200 active:bg-stone-200 transition duration-150 ease-in-out'
            : 'h-9 inline-flex gap-2 items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-600 hover:text-stone-800 hover:bg-stone-200 focus:outline-none focus:bg-stone-200 active:bg-stone-200 transition duration-150 ease-in-out';
@endphp

<a wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}

    @if ($active)
        <span class="absolute left-0 -bottom-4 w-full rounded bg-green-500 h-0.5"></span>
    @endif
</a>
