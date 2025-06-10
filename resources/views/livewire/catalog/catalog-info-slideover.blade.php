
<x-slideover  :title="$catalog->title" description="" class="max-w-2xl">
    
    <div class="prose min-h-28">{{ $catalog->description }}</div>


    <div class="space-y-4">
        <div class="space-y-2">
            <h4>{{ __('Statistics') }}</h4>

            <p>{{ trans_choice(':total entry|:total entries', $catalog->entries_count, ['total' => $catalog->entries_count]) }}</p>
        </div>

        <div class="space-y-2">
            <h4>{{ __('Owner') }}</h4>

            <div class="flex gap-2">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <img class=" h-7 w-7 rounded-full object-cover" src="{{ $catalog->user->profile_photo_url }}" aria-hidden="true" />
                @endif
                <p>
                    <span class="block font-medium">{{ $catalog->user->name }}</span>
                    <span class="block text-xs">{{ $catalog->team->name }}</span>
                </p>
            </div>
        </div>

        <div class="space-y-2">
            <h4>{{ __('Sharing') }}</h4>

            <x-document-visibility-badge :value="$catalog->visibility" />
        </div>

    </div>

    
    <x-slot name="actions">
        @can('update', $catalog)
            <x-button  type="button" x-data x-on:click="Livewire.dispatch('openSlideover', {component: 'catalog.edit-catalog-slideover', arguments: {catalog: '{{ $catalog->getKey() }}'}})">
                {{ __('Modify catalog') }}
            </x-button>
        @endcan
    </x-slot>
    
    
</x-slideover>