@props(['disabled' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['rows' => 6, 'class' => 'border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm']) !!}>{{ $slot }}</textarea>
