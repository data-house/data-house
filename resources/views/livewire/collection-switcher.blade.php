<x-dropdown align="right" width="w-96">
    <x-slot name="trigger">
        <x-button class="justify-self-end inline-flex gap-1 items-center">
            <x-heroicon-m-rectangle-stack class="w-4 h-4" />
            {{ __('Collections') }}
        </x-button>
    </x-slot>

    <x-slot name="content">
        <div class="relative w-full text-base font-normal min-h-[24rem] max-h-[24rem] flex flex-col">

            <div>
                <div class="flex justify-between items-center px-4">
                    <h4 class="text-stone-700 font-semibold">
                        {{ __('Collections') }}
                    </h4>
                    
                    <livewire:create-collection />
                </div>
                
                <div class="mt-1 px-4 basis-full prose prose-sm prose-stone prose-p:text-sm prose-p:mb-0">
                    <p class="">{{ __('A document can simultaneously belong to multiple collections. The same document is synced across all collections to which it belongs in order to avoid duplicates.') }}</p>
                </div>
            </div>

            <div class="mt-2 grow overflow-y-auto">

                @foreach ($this->collections as $collection)
                    <x-dropdown-link x-tooltip.raw="{{ $collection->firstNote?->previewContent()}}" class="inline-flex gap-2 items-center"
                        href="{{ route('collections.show', $collection) }}"
                        :active="request()->is('*/'.$collection->ulid)">
                        <x-heroicon-o-archive-box class="w-6 h-6  {{ request()->is('*/'.$collection->ulid) ? 'text-lime-600' : 'text-stone-600' }}" />
                        {{ $collection->title }}
                    </x-dropdown-link>
                @endforeach
            </div>
        </div>
    </x-slot>
</x-dropdown>
