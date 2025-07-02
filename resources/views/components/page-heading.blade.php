@props(['title'])

<div class="">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">        
        <h1 class="grow text-lg font-semibold leading-7 text-stone-900 whitespace-nowrap">{{ $title }}</h1>

        @if (isset($actions) && $actions->isNotEmpty())
        <div class="flex space-x-4  items-center justify-between sm:justify-end ">

            {{ $actions ?? null}}
        </div>
        @endif
    </div>
    
    @if (isset($slot) && $slot->isNotEmpty())    
        <div class="mt-3 flex gap-3 md:gap-4 text-sm font-semibold leading-6 grow sm:leading-7">
            {{ $slot ?? null}}
        </div>
    @endif
</div>