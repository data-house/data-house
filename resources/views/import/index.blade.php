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
                            <td class="py-2 w-8/12">{{ __('Import') }}</td>
                            <td class="py-2 w-2/12">{{ __('Mappings') }}</td>
                            <td class="py-2 w-2/12">{{ __('Status') }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($imports as $import)

                            <tr>
                                <td class="py-2">
                                    <p>
                                        <a class="underline" href="{{ route('imports.show', $import) }}">{{ $import->label() }}</a>
                                        <span class="inline-flex gap-1 text-sm font-mono items-center px-2 py-0.5 rounded-xl bg-stone-200 text-stone-900">
                                            {{ $import->source->name }}
                                        </span>
                                    </p>
                                </td>
                                <td class="py-2">{{ $import->maps_count }}</td>
                                <td class="py-2"><x-status-badge :status="$import->status" /></td>
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
