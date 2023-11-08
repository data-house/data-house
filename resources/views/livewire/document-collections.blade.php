<div class="space-y-1">
    <div class="flex flex-wrap gap-4">
        @forelse ($collections as $collection)
            <div class="inline-flex gap-2 py-0.5 px-2 items-center rounded-md bg-lime-100 hover:bg-lime-200 ring-1 ring-lime-500">
                <a href="{{ $collection->url() }}" class="hover:underline">{{ $collection->title }}</a>
                <button wire:click="remove({{$collection->getKey()}})" class="rounded-full p-0.5 hover:bg-white hover:text-lime-900 shrink-0" title="{{ __('Remove document from :collection', ['collection' => $collection->title]) }}">
                    <x-heroicon-m-x-mark class="w-4 h-4" />
                </button>
            </div>
        @empty
            <p class="text-stone-700">{{ __('Not in collection') }}</p>
        @endforelse
    </div>
    <div>
        <x-dropdown align="right" width="w-96">
            <x-slot name="trigger">
                <button class="inline-flex items-center gap-2">
                    <x-heroicon-m-plus class="w-4 h-4" />
                    {{ __('Attach a collection ')}}
                </button>
            </x-slot>
            <x-slot name="content">
                <x-input-error for="collection" class="p-4" />
                <div class="relative w-full text-base font-normal min-h-[12rem] max-h-[12rem] overflow-y-auto {{ $selectableCollections->isEmpty() ? 'grid content-center' : ''}}">
                    @forelse ($selectableCollections as $collection)
                        <button class="inline-flex gap-2 items-center w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100"
                            wire:click="add({{$collection->getKey()}})">
                            <x-heroicon-o-archive-box class="w-6 h-6 text-stone-600" />
                            {{ $collection->title }}
                        </button>
                    @empty
                        <div class="p-2 flex flex-col items-center">
                            <x-heroicon-o-rectangle-stack class="w-20 h-20 text-stone-300" />
                            <p class="text-stone-600 text-center px-10">{{ __('The document is already in all available collections') }}</p>
                        </div>
                    @endforelse
                </div>
            </x-slot>
        </x-dropdown>
    </div>
</div>
