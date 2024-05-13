<x-app-layout>
    <x-slot name="title">
        {{ __('Edit :document', ['document' => $document->title]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Edit :document', ['document' => $document->title]) }}
            </h2>
            <div class="flex gap-2">

                <div class="ml-3 relative">
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

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <form action="{{ route('documents.update', $document) }}" method="post" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-label for="title" value="{{ __('Document title') }}" />
                    <x-input-error for="title" class="mt-2" />
                    <x-input id="title" type="text" name="title" class="mt-1 block w-full max-w-prose" autocomplete="title" value="{{ old('title', $document->title) }}" />
                </div>
                    
                <div>
                    <x-label for="description" value="{{ __('Abstract') }}" />
                    <x-input-error for="description" class="mt-2" />
                    <x-textarea id="description" type="text" name="description" class="mt-1 block w-full max-w-prose" autocomplete="abstract">{{ old('description', $document->latestSummary?->text) }}</x-textarea>
                </div>

                <div class="flex items-center gap-4">
                    <x-button class="">
                        {{ __('Save') }}
                    </x-button>

                    <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('documents.show', $document) }}">
                        {{ __('Cancel') }}
                    </a>
                </div>

            </form>



        </div>
    </div>
</x-app-layout>
