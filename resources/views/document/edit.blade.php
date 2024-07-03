<x-app-layout>
    <x-slot name="title">
        {{ __('Edit :document', ['document' => $document->title]) }}
    </x-slot>
    <x-slot name="header">
        <div class="space-y-2 md:space-y-0 flex flex-col md:flex-row md:justify-between gap-2 relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('documents.show', $document) }}" class="px-1 py-0.5 bg-blue-50 rounded text-base inline-flex items-center text-blue-700 underline hover:text-blue-800" title="{{ __('Back to :entry', ['entry' => $document->title]) }}">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ $document->title }}
                </a>
                {{ __('Edit') }}
            </h2>
            <div class="md:flex gap-2 hidden ml-3 relative">

                @can('delete', $document)
                    <x-dropdown align="right" width="min-w-[448px] w-[100vw] md:w-[448px]" contentClasses="bg-white">
                        <x-slot name="trigger">
                            <x-danger-button type="button">
                                {{ __('Delete document') }}
                            </x-danger-button>
                        </x-slot>

                        <x-slot name="content">

                            <div class="">
                                <form action="{{ route('documents.destroy', $document) }}" method="post" class="">
                                    @csrf
                                    @method('DELETE')
    
                                    <div class="py-4 text-center sm:mt-0 sm:ml-4 sm:text-left px-4">
                                        <h3 class="text-lg font-medium text-stone-900">
                                            {{ __('Delete document') }}
                                        </h3>
                        
                                        <div class="mt-4 text-sm text-stone-600 ">
                                            <p class="break-all">{{ __('Delete ":document" from the digital library?', ['document' => $document->title]) }}</p>
                                            <p>{{ __('This will remove also the document in the document management system.') }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-row justify-end px-4 py-2 bg-stone-100 text-right rounded-b-md">
                                        <x-secondary-button type="button" @click="open = ! open">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>
                            
                                        <x-danger-button  type="submit" class="ml-3">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </div>
    
                                </form>


                            </div>

                        </x-slot>
                    </x-dropdown>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="">
        <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-0">

            <x-section submit="{{ route('documents.update', $document) }}">

                <x-slot name="title">
                    {{ __('Document Title') }}
                </x-slot>

                <x-slot name="description">
                    {{ __('The document\'s title information.') }}
                </x-slot>

                <x-slot name="form">

                    @csrf
                    @method('PUT')

                    <div class="col-span-6 sm:col-span-4">
                        <x-label for="title" value="{{ __('Document title') }}" />
                        <x-input-error for="title" class="mt-2" />
                        <x-input id="title" type="text" name="title" class="mt-1 block w-full max-w-prose" autocomplete="title" value="{{ old('title', $document->title) }}" />
                    </div>

                </x-slot>
                    
                <x-slot name="actions">
                    <x-button class="">
                        {{ __('Save') }}
                    </x-button>
                </x-slot>

            </x-section>

            <x-section-border />

            <x-section-no-form>
                <x-slot name="title">{{ __('Summaries') }}</x-slot>
                <x-slot name="description">{{ __('Manage summaries and abstract generated with the help of AI or by users.') }}</x-slot>
            
                <div class="col-span-6">

                    @include('document.partials.summary-buttons')

                    <livewire:document-summaries-viewer :show-all="true" :document="$document" />

                </div>
            </x-section-no-form>


            <div class="md:hidden">
                @can('delete', $document)
                <x-section-border />
                    <x-dropdown align="right" width="min-w-[448px] w-[100vw] md:w-[448px]" contentClasses="bg-white">
                        <x-slot name="trigger">
                            <x-danger-button type="button">
                                {{ __('Delete document') }}
                            </x-danger-button>
                        </x-slot>

                        <x-slot name="content">

                            <div class="">
                                <form action="{{ route('documents.destroy', $document) }}" method="post" class="">
                                    @csrf
                                    @method('DELETE')
    
                                    <div class="py-4 text-center sm:mt-0 sm:ml-4 sm:text-left px-4">
                                        <h3 class="text-lg font-medium text-stone-900">
                                            {{ __('Delete document') }}
                                        </h3>
                        
                                        <div class="mt-4 text-sm text-stone-600 ">
                                            <p class="break-all">{{ __('Delete ":document" from the digital library?', ['document' => $document->title]) }}</p>
                                            <p>{{ __('This will remove also the document in the document management system.') }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-row justify-end px-4 py-2 bg-stone-100 text-right rounded-b-md">
                                        <x-secondary-button type="button" @click="open = ! open">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>
                            
                                        <x-danger-button  type="submit" class="ml-3">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </div>
    
                                </form>


                            </div>

                        </x-slot>
                    </x-dropdown>
                @endcan
            </div>

        </div>
    </div>
</x-app-layout>
