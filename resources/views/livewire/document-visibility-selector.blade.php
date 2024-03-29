<x-dropdown align="right" width="60">
    <x-slot name="trigger">

        <x-button class="gap-1" title="{{ __('Currently accessibly by: :value', ['value' => $visibility->label()])}}">
            <x-heroicon-o-eye class="w-3 h-3 shrink-0" />
            {{ __('Change Visibility')}} <x-heroicon-o-chevron-down class="w-5 h-5" />
        </x-button>

    </x-slot>

    <x-slot name="content">
        <div class="min-w-[20rem]">

            <div class="block px-4 py-2 text-xs text-stone-400">
                {{ __('Current Visibility') }}
            </div>
        
            @if ($visibility === \App\Models\Visibility::TEAM && $team)
                <p class=" px-4 pt-2 font-medium">
                    "{{ $team }}"
                </p>
                <p class=" px-4 pb-2 font-medium">
                    {{ $visibility->label() }}
                </p>
            @else
                <p class=" px-4 py-2 font-medium">
                    {{ $visibility->label() }}
                </p>
            @endif

            <div class="my-4 border-t border-stone-200"></div>

            <div class="block px-4 py-2 text-xs text-stone-400">
                {{ __('Change Visibility') }}
            </div>

            <form wire:submit="save">

                <x-input-error class="px-4 py-2" for="selectedVisibility" />

                @foreach ($options as $item)

                    <label for="cv-{{$item->name}}" class="w-full px-4 py-2 text-left text-sm leading-5 transition duration-150 ease-in-out block {{ $selectedVisibility === $item->value ? 'text-lime-900 bg-lime-100 hover:bg-lime-200 focus:bg-lime-200 focus-within:bg-lime-200' : 'text-stone-700 hover:bg-stone-100 focus:bg-stone-100 focus-within:bg-stone-100' }}">
                        <x-radio
                            id="cv-{{$item->name}}"
                            name="cv-selector"
                            wire:key="cv-{{$item->name}}"
                            wire:model="selectedVisibility"
                            :value="$item->value"
                            {{-- :checked="$visibility === $item" --}}
                            />
                        {{ $item->label() }}
                    </label>
                @endforeach

                <x-button type="submit" class="mx-4 my-2 grow">{{ __('Save') }}</x-button>
            </form>

        </div>
    </x-slot>
</x-dropdown>
