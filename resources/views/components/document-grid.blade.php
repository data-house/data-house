@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'grid grid-cols-3 gap-4'])}}>
    @forelse ($documents as $document)
        <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
            <div class="flex gap-2 justify-start">
                @if ($document->type)
                    <span class="inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->type->name }}</span>
                @endif
                
                <x-document-visibility-badge :value="$document->visibility" />
            </div>
            <div class="aspect-video bg-white -mx-4 -mt-4 flex items-center justify-center">
                {{-- Space for the thumbnail --}}
                <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
            </div>

            <a href="{{ route('documents.show', $document) }}" class="block font-bold truncate group-hover:text-blue-800">
                <span class="z-10 absolute inset-0"></span>{{ $document->title }}
            </a>
            @if ($document->project)
                <p class="my-2 truncate">
                    <span>{{ $document->project->countries()->pluck('value')->join(', ') }}</span>
                </p>
                <p class="my-2">
                    <a href="{{ route('projects.show', $document->project) }}" title="{{ $document->project->title }}" class="z-20 shrink-0 text-xs font-normal inline-block px-2 py-1 rounded bg-yellow-100 text-yellow-700">{{ $document->project->slug }}</a>

                </p>
                <p class="flex items-start gap-2 my-2">
                    @foreach ($document->project->topics as $topic)
                        <span class="inline-flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-stone-100 text-stone-900">
                            <x-heroicon-o-hashtag class="w-4 h-4" />
                            {{ $topic }}
                        </span>
                    @endforeach
                </p>
            @endif
        </div>
    @empty
        <div class="col-span-3">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
