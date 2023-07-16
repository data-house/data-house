@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center text-lime-700 transition duration-150 ease-in-out border-b-2 border-transparent hover:border-lime-300 focus:outline-none focus:text-lime-800 focus:border-lime-300 '
            : 'inline-flex items-center text-stone-700 transition duration-150 ease-in-out border-b-2 border-transparent hover:border-stone-300 focus:outline-none focus:text-stone-700 focus:border-lime-300 ';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
