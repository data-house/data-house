<x-app-layout>
    <x-slot name="title">
        {{ __('New mapping') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Create an import mapping for :import', ['import' => $import->source->name]) }}
                <p class="text-sm font-mono text-stone-700">{{ $import->configuration['url'] ?? '' }}</p>
            </h2>
            <div class="flex gap-2">
                
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">                
            <div class="">
                <form action="{{ route('imports.mappings.store', $import) }}" method="post" class="space-y-4">
                    @csrf

                    <div>
                        <x-label for="paths" value="{{ __('Select the folder(s) you want to import') }}" />
                        <x-input-error for="paths" class="mt-2" />

                        <livewire:import-source-browser :import="$import" />
                    </div>

                    @include('import-map.partials.form')
    
                    <div class="flex items-center gap-4">
                        <x-button type="submit" >
                            {{ __('Create folder mapping') }}
                        </x-button>
    
                        <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('imports.show', $import) }}">
                            {{ __('Cancel') }}
                        </a>
                    </div>
    
                </form>

                
            </div>
        </div>
    </div>
</x-app-layout>
