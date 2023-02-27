@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-lime-400 text-left text-base font-medium text-lime-700 bg-lime-50 focus:outline-none focus:text-lime-800 focus:bg-lime-100 focus:border-lime-700 transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-stone-600 hover:text-stone-800 hover:bg-stone-50 hover:border-stone-300 focus:outline-none focus:text-stone-800 focus:bg-stone-50 focus:border-stone-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
