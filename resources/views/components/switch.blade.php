@props(['id'])

@php
$id = $id ?? md5($attributes->wire('model'));
$model = $attributes->get('wire:model');
@endphp

<div x-data="{ switchOn: $wire.entangle('{{ $model }}').live }" class="flex space-x-2 {{ isset($description) ? 'items-start' : 'items-center' }}">
    <input id="thisId" type="checkbox" name="switch" class="hidden" :checked="switchOn">

    <button 
        x-ref="sw_{{$id}}"
        type="button" 
        @click="switchOn = ! switchOn"
        :class="switchOn ? 'bg-blue-600' : 'bg-neutral-200'" 
        class="shrink-0 relative inline-flex h-6 py-0.5 focus:outline-none rounded-full w-10 {{ isset($description) ? 'mt-0.5' : '' }}"
        x-cloak>
        <span :class="switchOn ? 'translate-x-[18px]' : 'translate-x-0.5'" class="w-5 h-5 duration-200 ease-in-out bg-white rounded-full shadow-md"></span>
    </button>

    <div>
        <label @click="$refs.sw_{{$id}}.click(); $refs.sw_{{$id}}.focus()" :id="$id('switch')" 
            {{-- :class="{ 'text-blue-600': switchOn, 'text-gray-400': ! switchOn }" --}}
            class="font-medium text-stone-900 text-sm select-none"
            x-cloak>
            {{ $slot }}
        </label>

        @if (isset($description))
        <p class="mt-1 text-sm text-stone-600 max-w-md">
            {{ $description }}
        </p>
        @endif
    </div>
</div>

