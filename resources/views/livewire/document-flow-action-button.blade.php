<x-popover width="80">
    <x-slot name="trigger" class="inline-flex items-center gap-1 px-4 py-2 bg-white border border-stone-300 rounded-md font-semibold text-xs text-stone-700  shadow hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">

        <x-heroicon-o-arrow-turn-down-right class="ms-1 size-4 text-stone-500" />

        {{ __('Actions') }}

        <x-heroicon-o-chevron-down class="size-4" />
            
    </x-slot>

    @forelse ($flows as $flow)
        <p class="flex" wire:key="{{ $flow->id }}">
            <button type="button" wire:click="showFlow('{{ $flow->uuid }}')" class="grow inline-flex items-center gap-1 pl-4 pr-2 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                {{ $flow->title }}
            </button>

            @if ($flow->runs_count > 0)
                <button type="button" wire:click="showFlow('{{ $flow->uuid }}')" class="inline-flex items-center gap-1 pr-4 pl-2 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                    <x-status-badge :status="\App\Models\ImportStatus::RUNNING" />
                </button>
            @else
                <button type="button" wire:click="triggerFlow('{{ $flow->uuid }}')" class="inline-flex items-center gap-1 pl-2 pr-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                    {{ __('Play') }}
                </button>
            @endif
        </p>
    @empty
        <p class="text-stone-600">{{ __('No flows defined.') }}</p>
    @endforelse



</x-popover>