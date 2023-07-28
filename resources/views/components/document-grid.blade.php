@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-4'])}}>
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
                    <span class="inline-block text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">{{ __('pending review') }}</span>
                @endif
            </p>
        </div>
    @empty
        <div class="col-span-3">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
