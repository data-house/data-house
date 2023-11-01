<x-app-layout>
    <x-slot name="title">
        {{ __(':label - Import', ['label' => $import->label()]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                <a href="{{ route('imports.index') }}" class="px-1 py-0.5 bg-blue-50 rounded text-base inline-flex items-center text-blue-700 underline hover:text-blue-800" title="{{ __('Back to the import list') }}">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ __('Imports') }}
                </a>
                {{ $import->label() }}
                <span class="inline-flex gap-1 text-xs items-center px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">
                    {{ $import->source->name }}
                </span>
            </h2>
            <div class="flex gap-2">
                @can('update', $import)
                    <x-button-link href="{{ route('imports.edit', $import) }}">
                        {{ __('Edit configuration') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="max-w-prose">{{ __('Below are the mappings you have created for this import. Remember, you can add different folders with various configurations without re-auth to the service (click "Import another folder" below). When you prepared all your imports, click the "Start import" button below.') }}</p>
            
            <div class="max-w-5xl">

                <div class="mb-6">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <td class="p-2">Source</td>
                                <td class="p-2">Target</td>
                                <td class="p-2">Status</td>
                                <td class="p-2">Action</td>
                            </tr>
                        </thead>
                        <tbody>

                            @foreach ($import->maps as $mapping)
                                
                                <tr>
                                    <td class="p-2">{{ $mapping->label() }}</td>
                                    <td class="p-2">{{ $mapping->mappedTeam->name }}</td>
                                    <td class="p-2">{{ $mapping->status->name }}</td>
                                    <td class="p-2">
                                        @can('view', $mapping)
                                            <a class="underline" href="{{ route('mappings.show', $mapping) }}">{{ __('View') }}</a>
                                        @endcan
                                        @can('update', $mapping)
                                            <a class="underline" href="{{ route('mappings.edit', $mapping) }}">{{ __('Edit') }}</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


                <div class="flex justify-between">
                    <x-button-link href="{{ route('imports.mappings.create', $import) }}">
                        {{ __('Import another folder') }}
                    </x-button-link>
                    
                    @can('update', $import)
                        <form action="{{ route('imports.start') }}" method="post">
                            @csrf

                            <input type="hidden" name="import" value="{{ $import->getKey() }}">
                            
                            <x-button>
                                {{ __('Start Import') }}
                            </x-button>
                        </form>
                    @endcan

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
