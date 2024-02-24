@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-4'])}}>
    @forelse ($documents as $document)
        <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
            <div class="flex gap-2 justify-start">
                
                @if ($document->format)
                    <span class="inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->format->name }}</span>
                @endif
                
                @feature(Flag::editDocumentVisibility())
                    <x-document-visibility-badge :value="$document->visibility" />
                @endfeature
                &nbsp;
            </div>
            <div class="aspect-video bg-white -mx-4 -mt-4 flex items-center justify-center overflow-hidden">
                {{-- Space for the thumbnail --}}
                @if ($document->hasThumbnail())
                    <img loading="lazy" class="aspect-video object-contain" src="{{ $document->thumbnailUrl() }}" aria-hidden="true">
                @else
                    <x-dynamic-component :component="$document->format->icon" class="text-gray-400 h-10 w-10" />
                @endif
            </div>

            <a href="{{ route('documents.show', $document) }}" class="block font-bold truncate group-hover:text-blue-800">
                <span class="z-10 absolute inset-0"></span>{{ $document->title }}
            </a>
            @if ($document->project)
                <p class="my-2">
                    <a href="{{ route('projects.show', $document->project) }}" class="z-20 shrink-0 text-xs font-normal inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-700">{{ $document->project->title }}</a>

                </p>
            @endif
        </div>
    @empty
        <div class="col-span-3">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
