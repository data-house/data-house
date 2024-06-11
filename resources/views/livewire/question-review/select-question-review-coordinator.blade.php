<div class="space-y-3">
    
    <x-dropdown align="left" width="w-96">
        <x-slot name="trigger">
            <button x-tooltip.raw="{{ __('Change review coordinator')}}" class="group/reviewers text-sm justify-between inline-flex gap-1 items-center text-stone-600 px-1 py-0.5 border border-transparent rounded-md  hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <span class="font-bold text-stone-700">{{ __('Coordinator') }}</span>

                <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-stone-600" />
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="p-2 text-sm">
                <p class="font-bold mb-3">{{ __('Assign a review coordinator') }}</p>

                <x-input-error for="selectedCoordinator" class="mb-2" />

                @forelse ($this->availableCoordinators as $item)
    
                    <label class="flex items-center gap-2">
                        <x-radio wire:model="selectedCoordinator" :value="$item->getKey()"/>
                        <x-user :user="$item" />
                    </label>
    
                @empty
                    <p class="text-stone-600">{{ __('No users eligible as coordinator.') }}</p>
                @endforelse

                @if($this->availableCoordinators->isNotEmpty())
                    <x-button type="button" class="w-full justify-center mt-2" wire:click="save">{{ __('Save') }}</x-button>
                @endif
            </div>
            <div class="p-2 mt-3 border-t border-stone-200">
                <x-small-button type="button" wire:click="removeCoordinator">{{ __('Clear assigned coordinator.') }}</x-small-button>
            </div>
        </x-slot>
    </x-dropdown>
    
    

    @if (is_null($this->coordinator) || !$this->coordinator->exists())
        <p class="prose">
            {{ __('No coordinator appointed.') }} <x-small-button type="button" wire:click="assignMyselfAsCoordinator">{{ __('Assign to me') }}</x-small-button>
        </p>
    @else
        <p>
            <x-user :user="$this->coordinator" />
        </p>
    @endif
</div>