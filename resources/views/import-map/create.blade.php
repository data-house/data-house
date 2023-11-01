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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div class="">
                <form action="{{ route('imports.mappings.store', $import) }}" method="post" class="space-y-4">
                    @csrf

                    <div>
                        <x-label for="paths" value="{{ __('Select the folder(s) you want to import') }}" />
                        <x-input-error for="paths" class="mt-2" />

                        <livewire:import-source-browser :import="$import" />
                    </div>
    
                    <div>
                        <x-label for="recursive" value="{{ __('Sub-folders handling') }}" />
                        <x-input-error for="recursive" class="mt-2" />
                        
                        <label for="recursive" class="flex items-center">
                            <x-checkbox id="recursive" name="recursive" value="1" />
                            <span class="ml-2 text-stone-800">{{ __('Recursively import all files in sub-folders') }}</span>
                        </label>
                    </div>

                    <div>
                        <x-label for="team" value="{{ __('Target Team') }}" />
                        <x-input-error for="team" class="mt-2" />
                        
                        <select name="team" id="team" class="mt-1 block w-full border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm">
                            @foreach ($teams as $team)
                                <option value="{{ $team->getKey() }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                        
                    <div>
                        <x-label for="description" value="{{ __('Uploader') }}" />
                        
                        <p>{{ __('Imported documents will appear as uploaded by :name', ['name' => $uploader->name]) }}</p>
                    </div>
    
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
