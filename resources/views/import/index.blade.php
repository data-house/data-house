<x-app-layout>
    <x-slot name="title">
        {{ __('Import documents') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Import document into the digital library') }}
            </h2>
            <div class="flex gap-2">
                @can('create', \App\Model\Import::class)
                    <x-button-link href="{{ route('imports.create') }}">
                        {{ __('New Import') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p>{{ __('Below are the import sources configured. Each import source can have multiple mappings defined.') }}</p>             
            <div class="max-w-5xl">

                <table class="w-full">
                    <thead>
                        <tr>
                            <td>Source</td>
                            <td>Status</td>
                            <td>Action</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($imports as $import)

                            <tr>
                                <td>
                                    <p><a href="{{ route('imports.show', $import) }}">{{ $import->source->name }}</a></p>
                                    <p class="text-xs font-mono text-stone-700">{{ $import->configuration['url'] ?? '' }}</p>
                                </td>
                                <td>{{ $import->status->name }}</td>
                                <td><a href="{{ route('imports.show', $import) }}">{{ __('View') }}</a></td>
                            </tr>
                    
                        @empty
                            

                            <tr>
                                <td colspan="3">
                                    @can('create', \App\Model\Import::class)
                                        <x-button-link href="{{ route('imports.create') }}">
                                            {{ __('Configure a new Import') }}
                                        </x-button-link>
                                    @endcan
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>                
            </div>
        </div>
    </div>
</x-app-layout>
