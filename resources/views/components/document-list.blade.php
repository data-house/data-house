@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4'])}}>
    <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden px-4 text-sm text-stone-700">
            
        <div class="col-span-5 flex gap-2 items-center">
            <div class="h-8 w-h-8" ></div>
            
            {{ __('Document') }}
        </div>

        <div class="flex gap-2 justify-start">
            {{ __('Type') }}
        </div>

        <div class="truncate col-span-4">
            {{ __('Project and region') }}
        </div>

        @feature(Flag::editDocumentVisibility())
        <div class="justify-start  col-span-2">
            {{ __('Access') }}
        </div>
        @endfeature
        
    </div>
    @forelse ($documents as $document)
        <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
            
            <div class="col-span-5 flex gap-2 items-center">
                <x-codicon-file-pdf class="text-gray-400 h-8 w-h-8" />
                
                <a href="{{ route('documents.show', $document) }}" class=" block font-bold truncate group-hover:text-blue-800">
                    <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                </a>
            </div>

            
            <div class="flex gap-2 justify-start">
                @if ($document->type)
                    <span class="truncate inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->type->name }}</span>
                @endif
            </div>

            <div class="flex truncate col-span-4">
                @if ($document->project)
                    <span class="truncate font-mono whitespace-nowrap text-sm px-2 py-1 rounded bg-yellow-100 text-yellow-700">
                    {{ $document->project->slug }}
                    </span>
                    <span class="whitespace-nowrap text-sm px-2 py-1">
                        {{ $document->project->facetRegions()->first() }}
                    </span>
                @endif
            </div>

            @feature(Flag::editDocumentVisibility())
            <div class="col-span-2">
                <x-document-visibility-badge :value="$document->visibility" />
            </div>
            @endfeature
        </div>
    @empty
        <div class="">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
