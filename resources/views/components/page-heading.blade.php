@props(['title'])

<div class="flex items-center justify-between">
    <div class="text-lg flex flex-wrap items-center gap-6 sm:flex-nowrap">
        <h1 class="font-semibold leading-7 text-stone-900">{{ $title }}</h1>

        @if (isset($slot) && !empty($slot))    
            <div class="order-last flex gap-x-8 text-sm font-semibold leading-6 sm:order-none sm:w-auto sm:border-l sm:border-gray-200 sm:pl-6 sm:leading-7">
                {{ $slot ?? null}}
            </div>
        @endif
    </div>

    <div class="flex gap-2">
        {{ $actions ?? null}}
    </div>
</div>