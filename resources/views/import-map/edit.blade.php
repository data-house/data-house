<x-app-layout>
    <x-slot name="title">
        {{ __('Edit mapping :source - Imports', ['source' => $mapping->label()]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                <a href="{{ route('mappings.show', $mapping) }}" class="px-1 py-0.5 bg-blue-50 rounded text-base inline-flex items-center text-blue-700 underline hover:text-blue-800" title="{{ __('Back to :import', ['import' => $mapping->label()]) }}">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ $mapping->label() }}
                </a>
                {{ __('Edit mapping', ['source' => $mapping->label()]) }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <form action="{{ route('mappings.update', $mapping) }}" method="post" class="space-y-4">
                @method('PUT')
                @csrf

                <div>
                    <x-label for="paths" value="{{ __('Selected folder(s) to import') }}" />

                    <ul>
                        @foreach ($paths as $folder)
                            <li class="flex gap-2 items-center">
                                <x-codicon-folder class="text-stone-400 h-5 w-h-5" />
                                {{ $folder }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                @include('import-map.partials.form')
    
                <div class="flex items-center gap-4">
                    <x-button type="submit" >
                        {{ __('Update folder mapping') }}
                    </x-button>

                    <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('mappings.show', $mapping) }}">
                        {{ __('Cancel') }}
                    </a>
                </div>
    
            </form>

        </div>
    </div>
</x-app-layout>
