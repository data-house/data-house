@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 pt-6 sm:pt-0'])}}>
    <div class="hidden sm:grid grid-cols-12 gap-2 items-center rounded overflow-hidden px-4 text-sm text-stone-700">
            
        <div
            @class([ 
            'flex',
            'gap-2',
            'items-center',
            'col-span-12 sm:col-span-7 md:col-span-6',
            ]) 
        >
            <div class="h-8 w-h-8" ></div>
            
            {{ __('Document') }}
        </div>

        <div
            @class([ 
            'hidden md:block md:col-span-1', 
            ])>
            {{ __('Format') }}
        </div>

        <div
            @class(['truncate', 
                'hidden sm:block sm:col-span-3'
                ])>
            {{ __('Project') }}
        </div>
        
        <div class="hidden sm:block sm:col-span-2">
            {{ __('Uploaded on') }}
        </div>
        
    </div>
    @forelse ($documents as $document)
        <div class="grid grid-rows-2 sm:grid-rows-1 sm:grid-cols-12 gap-2 sm:items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
            
            <div
                @class([ 
                'flex',
                'gap-2',
                'items-center',
                'sm:col-span-7 md:col-span-6',
                ]) 
            >
                <x-dynamic-component :component="$document->format->icon" class="text-gray-400 h-7 w-7 shrink-0" />
                
                <a href="{{ route('documents.show', $document) }}" class="min-w-0 block font-bold truncate group-hover:text-blue-800">
                    <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                </a>

                @feature(Flag::editDocumentVisibility())
                    <x-document-visibility-badge :value="$document->visibility" />
                @endfeature
            </div>

            <div
                @class([ 
                'hidden md:block md:col-span-1'
                ])>
                
                <span class="truncate inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->format->name }}</span>
                
            </div>

            <div
            @class(['truncate', 
                'hidden sm:block sm:col-span-3'
                ])>
                @if ($document->project)
                    <span class="truncate whitespace-nowrap text-sm">
                    {{ $document->project->title }}
                    </span>
                @endif
            </div>

            <div class="sm:col-span-2">
                {{ $document->created_at?->toDateString() }}
            </div>
        </div>
    @empty
        <div class="">
            <p>{{ $empty ?? __('No documents.') }}</p>
        </div>
    @endforelse

</div>
