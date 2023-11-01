<x-app-layout>
    <x-slot name="title">
        {{ __('Mapping :source - Imports', ['source' => $mapping->label()]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                <a href="{{ route('imports.show', $import) }}" class="px-1 py-0.5 bg-blue-50 rounded text-base inline-flex items-center text-blue-700 underline hover:text-blue-800" title="{{ __('Back to :import', ['import' => $import->label()]) }}">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ __('Import') }}
                </a>
                {{ __('Mapping for :source', ['source' => $mapping->label()]) }}
            </h2>
            <div class="flex gap-2">
                @can('update', $import)
                    <x-button-link href="{{ route('mappings.edit', $mapping) }}">
                        {{ __('Edit mapping') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="grid md:grid-cols-3 gap-2">

                <div class="bg-white p-2">
                    <p>{{ __('Status') }}</p>
                    {{ $mapping->status->name }}
                </div>

                <div class="bg-white p-2">
                    <p>{{ __('Paths') }}</p>
                    {{ join($mapping->filters['paths']) }}
                </div>
                <div class="bg-white p-2">
                    <p>{{ __('Configuration') }}</p>
                    @if ($mapping->recursive)
                        <span>{{ __('include files in sub-folders') }}</span>
                    @endif
                    @unless ($mapping->recursive)
                        <span>{{ __('only files in selected folder') }}</span>
                    @endunless
                </div>

            </div>


            <p class="max-w-prose">{{ __('Below are the documents that will be processed by this mapping. For each document you can see its status and source path.') }}</p>
            
            <div class="max-w-5xl">

                <div>
                    <table class="w-full">
                        <thead>
                            <tr>
                                <td>Source</td>
                                <td>Date</td>
                                <td>Size</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>{{ $document->source_path }} {{ $document->mime }}</td>
                                <td>{{ $document->document_date }}</td>
                                <td>{{ $document->document_size }}</td>
                                <td>{{ $document->status->name }}</td>

                            </tr>
                            
                        @empty
                            <tr>
                                <td colspan="4">

                                    @unless ($mapping->isStarted())
                                        {{ __('The list of documents to import will appear here once the mapping is processed.') }}
                                    @endunless
                                    @if ($mapping->isStarted())
                                        {{ __('Processing the mapping. The list of documents will appear here shortly.') }}
                                    @endif
                                
                                
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
