
<x-slideover  :title="$catalog->title" description="" class="max-w-2xl">
    
    <div class="prose mb-6">{{ $catalog->description }}</div>


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
            

            @if ($can_share)

                <p class="text-sm text-stone-600">{{ __('Choose who can access this catalog.') }}</p>

                <p>
                    <span wire:loading wire:target="changeVisibility">{{ __('Saving sharing preferences...') }}</span>
                </p>

                <div class="">

                    <div role="radiogroup" class="space-y-2">
                        <div class="flex items-center flex-wrap">
                            <input type="radio" 
                                id="visibility_personal" 
                                name="visibility" 
                                value="private" 
                                wire:click="changeVisibility(1)"
                                class="h-4 w-4 leading-4 border-stone-300 text-lime-600 shadow-sm focus:ring-lime-500"
                                @checked($catalog->visibility === \App\Models\Visibility::PERSONAL)
                                >
                            <label for="visibility_personal" class=" leading-4 ml-3 block text-sm font-medium text-gray-700">
                                {{ __('Only me') }}
                            </label>
                            <p class="ml-7 text-stone-600 basis-full text-sm">{{ __('I can only access this catalog.') }}</p>
                        </div>

                        <div class="flex items-center flex-wrap">
                            <input type="radio" 
                                id="visibility_team" 
                                name="visibility" 
                                value="team" 
                                wire:click="changeVisibility(2)"
                                class="h-4 w-4 border-stone-300 text-lime-600 shadow-sm focus:ring-lime-500"
                                @checked($catalog->visibility === \App\Models\Visibility::TEAM)
                                >
                            <label for="visibility_team" class="ml-3 block text-sm font-medium text-gray-700">
                                {{ __('Members of the team :name', ['name' => $catalog->team->name]) }}
                            </label>
                            <p class="ml-7 text-stone-600 basis-full text-sm">{{ __('I and other members of :name can access. Members with role :lower_role members are allowed to add new entries, while :upper_role can also add fields.', ['name' => $catalog->team->name, 'upper_role' => \App\Models\Role::MANAGER->label(), 'lower_role' => \App\Models\Role::MANAGER->label()]) }}</p>
                        </div>

                        <div class="flex items-center flex-wrap">
                            <input type="radio" 
                                id="visibility_authenticated" 
                                name="visibility" 
                                value="authenticated" 
                                wire:click="changeVisibility(3)"
                                class="h-4 w-4 border-stone-300 text-lime-600 shadow-sm focus:ring-lime-500"
                                @checked($catalog->visibility === \App\Models\Visibility::PROTECTED)
                                >
                            <label for="visibility_authenticated" class="ml-3 block text-sm font-medium text-gray-700">
                                {{ __('All authenticated users') }}
                            </label>
                            <p class="ml-7 text-stone-600 basis-full text-sm">{{ __('Catalog is visible to all users. Editing is still limited to me and members of :name.', ['name' => $catalog->team->name]) }}</p>
                        </div>
                    </div>
                </div>
                
            @else

                <p class="text-sm text-gray-600">{{ __('Who can access this catalog.') }}</p>

                <x-document-visibility-badge :value="$catalog->visibility" />
            @endif

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