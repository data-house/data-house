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

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="flex items-center flex-row gap-4 divide-x-2 mb-8">
                
                <x-status-badge :status="$mapping->status" size="text-sm" />

                <div class="pl-4 flex items-center">
                    @if ($mapping->isScheduled())
                        <x-codicon-sync class="text-stone-400 h-5 w-5 mr-2" />
                    @else
                        <x-codicon-sync-ignored class="text-stone-400 h-5 w-5 mr-2" />
                    @endif
                    {{ $mapping->schedule->label() }}
                    
                    @if ($mapping->isScheduled())
                        <span class="ml-4 text-sm">{{ __('next run') }}&nbsp;{{ $mapping->schedule->nextRunDate() }}</span>
                    @endif
                </div>

                

                <div class="pl-4 flex items-center">
                    @if ($mapping->recursive)
                        <x-codicon-file-submodule class="text-stone-400 h-5 w-5 mr-2" />
                        <span class="mr-4">{{ __('include files in sub-folders') }}</span>
                    @endif
                    @unless ($mapping->recursive)
                        <x-codicon-folder class="text-stone-400 h-5 w-5 mr-2" />
                        <span class="mr-4">{{ __('only files in selected folder') }}</span>
                    @endunless

                    <x-dropdown align="right" width="w-96">
                        <x-slot name="trigger">
                            <x-small-button class="justify-self-end inline-flex gap-1 items-center">
                                {{ __('View paths') }}
                            </x-small-button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="prose">
                                <ul>
                                    @foreach ($mapping->filters['paths'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

            </div>


            <p class="max-w-prose">{{ __('Below are the documents processed by this mapping. For each document you can see its status and source path.') }}</p>
            
            <div class="max-w-7xl">

                <div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <td class="w-7/12">Source</td>
                                <td class="w-1/12">Import date</td>
                                <td class="w-1/12">Document date</td>
                                <td class="w-1/12">Size</td>
                                <td class="w-2/12">Status</td>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($documents as $document)
                            <tr class="odd:bg-white mb-1">
                                <td class="py-1 pr-2">
                                    <span class="inline-block text-xs px-2 py-0.5 rounded bg-gray-200">{{ $document->mime }}</span>
                                    <p>{{ $document->source_path }}</p>
                                </td>
                                <td><x-date :value="$document->created_at" /></td>
                                <td><x-date :value="$document->document_date" /></td>
                                <td>{{ $document->document_size ? \Illuminate\Support\Number::fileSize($document->document_size) : '-' }}</td>
                                <td><x-status-badge :status="$document->status" /></td>

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

                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
