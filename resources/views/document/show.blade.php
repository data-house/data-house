<x-app-layout>
    <x-slot name="title">
        {{ $document->title }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ $document->title }}
            </h2>
            <div class="flex gap-2">
                @can('view', $document)
                    <x-button-link href="{{ $document->viewerUrl() }}" target="_blank">
                        {{ __('Open Document') }}
                    </x-button-link>
                @endcan
                @can('update', $document)
                    <x-button-link href="{{ route('documents.edit', $document) }}">
                        {{ __('Edit') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($hasActivePipelines)
                <div class="mb-4 bg-yellow-100 text-yellow-900 flex items-center gap-2 px-3 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm1.23-3.723a.75.75 0 00.219-.53V2.929a.75.75 0 00-1.5 0V5.36l-.31-.31A7 7 0 003.239 8.188a.75.75 0 101.448.389A5.5 5.5 0 0113.89 6.11l.311.31h-2.432a.75.75 0 000 1.5h4.243a.75.75 0 00.53-.219z" clip-rule="evenodd" />
                      </svg>
                      
                    <span class="">{{ __('Document under processing. Preview and search might not be available while the document is being processed.') }}</span>
                </div>
            @endif
            <div class="mb-4">
                @if ($document->draft)
                    <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                @endif
            </div>
            <div class="grid md:grid-cols-3">

                <div class="col-span-2">

                    @if ($document->description)
                        <div class="prose prose-green">
                            {!! \Illuminate\Support\Str::markdown($document->description) !!}
                        </div>
                    @else
                        <div class="prose prose-green">
                            {!! \Illuminate\Support\Str::markdown(__('This document doesn\'t have an abstract. [Be the first one to contribute](:url).', ['url' => route('documents.edit', $document)])) !!}
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="aspect-video bg-white flex items-center justify-center">
                        {{-- Space for the thumbnail --}}
                        <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-bold">{{ __('Details') }}</h4>
                        
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('File type') }}</span>{{ $document->mime }}</p>
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Uploaded by') }}</span>{{ $document->uploader->name }}</p>
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Team') }}</span>{{ $document->team?->name }}</p>
                        <p><span class="text-xs uppercase block text-stone-700">{{ __('Language') }}</span>{{ $document->languages?->join(',') }}</p>
                        
                    </div>

                    <div class="space-y-2">
                        <h4 class="font-bold">{{ __('Publication') }}</h4>

                        @if ($document->isPublished())
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Published at') }}</span>{{ $document->published_at }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Published by') }}</span>{{ $document->published_by?->name }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Reachable on') }}</span>{{ $document->published_to_url }}</p>
                        @else
                            <p class="prose">{{ __('Not yet published.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
