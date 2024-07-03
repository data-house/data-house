<x-app-layout>
    <x-slot name="title">
        {{ __('Edit :import', ['import' => $import->source->name]) }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Edit :import', ['import' => $import->source->name]) }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <form action="{{ route('imports.update', $import) }}" method="post" class="space-y-4">
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
                    <x-textarea id="description" type="text" name="description" class="mt-1 block w-full max-w-prose" autocomplete="abstract">{{ old('description', $document->description) }}</x-textarea>
                </div>

                <div class="flex items-center gap-4">
                    <x-button class="">
                        {{ __('Save') }}
                    </x-button>

                    <a class="underline text-sm text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500" href="{{ route('imports.show', $import) }}">
                        {{ __('Cancel') }}
                    </a>
                </div>

            </form>

        </div>
    </div>
</x-app-layout>
