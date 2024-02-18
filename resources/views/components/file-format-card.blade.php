@props(['format'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>

    <x-dynamic-component :component="$format->icon" class="text-gray-400 h-7 w-7 shrink-0" />

    {{ $format->name }}
    
</div>