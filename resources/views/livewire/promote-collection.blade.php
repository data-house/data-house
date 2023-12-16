<div class="space-y-1">

    <x-label for="promote" value="{{ __('Promote Collection') }}" />

    <x-input-error for="promote" class="mt-2" />

    @if ($collection_can_be_promoted && $collection_missing_team)

        <x-button class="" name="promote" type="button" wire:click="$toggle('confirmingPromotion')">
            {{ __('Promote to Team') }}
        </x-button>

        <p class="text-sm text-stone-700 font-bold">
            {{ __('Collection not linked to a team. It will be associated to the currently selected team.') }}
        </p>

        <p class="text-sm text-stone-700">
            {{ __('Promoting the collection to Team makes the collection accessible to all team members. You still retain ownership. You cannot undo the operation.') }}
        </p>

    @elseif ($collection_can_be_promoted)
        <x-button class="" name="promote" type="button" wire:click="$toggle('confirmingPromotion')">
            {{ __('Promote to Library') }}
        </x-button>

        <p class="text-sm text-stone-700">
            {{ __('Promoting the collection to Library makes the collection accessible to all authenticated users. You and your team still retain ownership. You cannot undo the operation.') }}
        </p>
    @endif
    
    @unless ($collection_can_be_promoted)
        <x-button disabled="true" class="" name="promote" type="button">
            {{ __('Already promoted') }}
        </x-button>

        <p class="text-sm text-stone-700">
            {{ __('The collection is already accessible by all authenticated users.') }}
        </p>
    @endunless


    <x-confirmation-modal :id="$collectionId" wire:model.live="confirmingPromotion">
        <x-slot name="title">
            {{ __('Confirm collection promotion') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to promote this collection? Once promoted its access level cannot be reduced.') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingPromotion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-3" wire:click="promote" wire:loading.attr="disabled">
                @if ($collection_can_be_promoted && $collection_missing_team)
                    {{ __('Promote to Team') }}
                @else
                    {{ __('Promote to Library') }}
                @endif
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>

                
</div>
