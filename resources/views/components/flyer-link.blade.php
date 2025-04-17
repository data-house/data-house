@props(['active' => false])

@php
$classes = ($active ?? false)
            ? 'text-stone-900 bg-lime-100 hover:bg-lime-200 focus:bg-lime-200'
            : 'text-stone-700 hover:bg-stone-100 focus:bg-stone-100';
@endphp

<a {{ $attributes->merge(['class' => 'inline-flex rounded items-center gap-2 w-full px-2 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out ' . $classes]) }}>{{ $slot }}</a>
