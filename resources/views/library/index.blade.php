<x-app-layout>
    <x-slot name="title">
        {{ __('Digital Library') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Digital Library') }}
            </h2>
            <div class="flex gap-2">
                @can('create', \App\Model\Document::class)
                    <x-button-link href="{{ route('documents.create') }}">
                        {{ __('Upload Document') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="" method="get">
                    <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search within the digital library...') }}" />
                </form>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                @forelse ($documents as $document)
                    <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
                        <div class="aspect-video bg-white -mx-4 -mt-4 flex items-center justify-center">
                            {{-- Space for the thumbnail --}}
                            <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                        </div>

                        <a href="{{ route('documents.show', $document) }}" class="block font-bold truncate group-hover:text-blue-800">
                            <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                        </a>
                        <p>{{ $document->created_at }}</p>
                        <p>
                            @if ($document->draft)
                                <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('draft') }}</span>
                            @endif
                        </p>
                    </div>
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No documents in the library.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
