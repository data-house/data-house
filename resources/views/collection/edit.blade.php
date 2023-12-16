<x-app-layout>
    <x-slot name="title">
        {{ __('Edit :collection', ['collection' => $collection->title]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Edit :collection', ['collection' => $collection->title]) }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

            <x-section submit="{{ route('collections.update', $collection) }}">

                <x-slot name="title">
                    {{ __('Collection Name') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('The collection\'s name and owner information.') }}
                </x-slot>

                <x-slot name="form">

                    @csrf
                    @method('PUT')

                    <div class="col-span-6 sm:col-span-4">

                        <x-label value="{{ __('Collection creator and team') }}" />
                        
                        <p>
                            {{ $owner_user->name }}
                        </p>

                        @if ($owner_team)
                            <p>
                                <span class="font-bold">{{ __('Team') }}</span> {{ $owner_team->name }}
                            </p>
                        @endif
                    </div>

                    @include('collection.partials.title')

                </x-slot>
                    
                <x-slot name="actions">
                    <x-button class="">
                        {{ __('Save') }}
                    </x-button>

                    <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('collections.show', $collection) }}">
                        {{ __('Cancel') }}
                    </a>
                </x-slot>

            </x-section>

            <x-section-border />

            <x-section-no-form>

                <x-slot name="title">
                    {{ __('Collection Access') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('Who can access the collection and its promotion to all authenticated users.') }}
                </x-slot>

                <div class="col-span-6 sm:col-span-4">

                    <x-label value="{{ __('Accessible by') }}" />

                    <x-document-visibility-badge class="mt-1" :value="$collection->visibility" />
                </div>


                <div class="col-span-6 sm:col-span-4">

                    <livewire:promote-collection :collection="$collection" />

                </div>

            </x-section-no-form>

        </div>
    </div>
</x-app-layout>
