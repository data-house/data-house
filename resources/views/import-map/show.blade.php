<x-app-layout>
    <x-slot name="title">
        {{ __('Import from :source', ['source' => $import->source->name]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Import from :source', ['source' => $import->source->name]) }}
                <p class="text-sm font-mono text-stone-700">{{ $import->configuration['url'] ?? '' }}</p>
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

                <div>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <td>Source</td>
                                <td>Target</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Source</td>
                                <td>Target</td>
                                <td>Action</td>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <div class="flex justify-between">
                    <x-button-link href="{{ route('imports.mappings.create', $import) }}">
                        {{ __('Import another folder') }}
                    </x-button-link>
                    
                    <x-button-link href="#">
                        {{ __('Start Import') }}
                    </x-button-link>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
