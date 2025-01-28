@props(['title', 'description' => null])

<div {{ $attributes->exceptProps(['wire:submit', 'action'])->merge(['class' => 'p-4 flex flex-col justify-between h-full']) }}>
    

    <form {{ $attributes->onlyProps(['wire:submit', 'action', 'method', 'enctype']) }} class="flex flex-col justify-between h-full">
        <x-section-title>
            <x-slot name="title">{{ $title }}</x-slot>
            <x-slot name="description">{{ $description }}</x-slot>
            <x-slot name="aside">

                {{ $aside ?? null }}
                
                <x-small-button type="button" wire:click="$dispatch('closeSlideover')">
                    {{ __('Close')}}
                </x-small-button>
            </x-slot>
        </x-section-title>

        <div class="mt-5 md:mt-0 md:col-span-2 grow overflow-y-auto">
                
            {{ $slot }}
            
        </div>
        @if (isset($actions))
            <div class="h-4 bg-stone-100 -mx-4">
                <div class="bg-white rounded-bl-lg rounded-br-lg h-4"></div>
            </div>
            <div class="-mx-4 -mb-4 flex items-center justify-end gap-2 px-4 py-3 bg-stone-100 text-right sm:px-6 rounded-bl-lg rounded-br-lg">
                {{ $actions }}
            </div>
        @endif
    </form>
</div>
