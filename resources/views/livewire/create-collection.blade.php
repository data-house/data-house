<div>
    @can('create', \App\Models\Collection::class)
                    
        <x-button-link wire:click.prevent="$toggle('currentlyCreatingCollection')" href="{{ route('collections.create') }}">
            {{ __('New') }}
        </x-button-link>

        <x-dialog-modal wire:model.live="currentlyCreatingCollection">
            <x-slot name="title">
                {{ __('Create collection') }}
            </x-slot>

            <x-slot name="content">
                <form wire:submit="createCollection">
                    <div class="relative z-0 mt-1 rounded-lg cursor-pointer">
                        <div class="">
                            <x-label for="title" value="{{ __('Collection name') }}" />
                            <x-input-error for="title" class="mt-2" />
                            <x-input id="title" type="text" wire:model="title" name="title" class="mt-1 block w-full" autofocus autocomplete="title" />
                        </div>
                        <div class="mt-4">
                            <x-label for="description" value="{{ __('Description') }}" />
                            <p class="text-stone-600 text-sm mt-1">{{ __('Describe how it is used and whether any rules apply to adding documents to the collection.') }}</p>
                            <x-input-error for="description" class="mt-2" />
                            <x-textarea id="description" wire:model="description" name="description" class="mt-1 block w-full" autocomplete="none" />
                        </div>
                    </div>
                </form>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="stopCreatingCollection" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-button class="ml-3" wire:click="createCollection" wire:loading.attr="disabled">
                    {{ __('Create') }}
                </x-button>
            </x-slot>
        </x-dialog-modal>

    @endcan
</div>
