@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4'])}}>
    <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden px-4 text-sm text-stone-700">
            
        <div
            @class([ 
            'flex',
            'gap-2',
            'items-center',
            'col-span-6',
            ]) 
        >
            <div class="h-8 w-h-8" ></div>
            
            {{ __('Document') }}
        </div>

        <div
            @class([ 
            'col-span-1', 
            ])>
            {{ __('Format') }}
        </div>

        <div
            @class(['truncate', 
                'col-span-3'
                ])>
            {{ __('Project') }}
        </div>
        
        <div class="col-span-2">
            {{ __('Uploaded on') }}
        </div>
        
    </div>
    @forelse ($documents as $document)
        <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
            
            <div
                @class([ 
                'flex',
                'gap-2',
                'items-center',
                'col-span-6',
                ]) 
            >
                <x-dynamic-component :component="$document->format->icon" class="text-gray-400 h-7 w-7 shrink-0" />
                
                <a href="{{ route('documents.show', $document) }}" class=" block font-bold truncate group-hover:text-blue-800">
                    <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                </a>

                @feature(Flag::editDocumentVisibility())
                    <x-document-visibility-badge :value="$document->visibility" />
                @endfeature
            </div>

            <div
                @class([ 
                'col-span-1'
                ])>
                
                <span class="truncate inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->format->name }}</span>
                
            </div>

            <div
            @class(['truncate', 
                'col-span-3'
                ])>
                @if ($document->project)
                    <span class="truncate whitespace-nowrap text-sm">
                    {{ $document->project->title }}
                    </span>
                @endif
            </div>

            <div class="col-span-2">
                {{ $document->created_at?->toDateString() }}
            </div>
        </div>
    @empty
        <div class="">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
