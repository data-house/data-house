@props(['documents', 'empty' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4'])}}>
    <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden px-4 text-sm text-stone-700">
            
        <div
            @class([ 
            'flex',
            'gap-2',
            'items-center',
            'col-span-7' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility()),
            'col-span-5' => \Laravel\Pennant\Feature::active(Flag::editDocumentVisibility()),
            ]) 
        >
            <div class="h-8 w-h-8" ></div>
            
            {{ __('Document') }}
        </div>

        <div
            @class([ 
            'col-span-2' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility())
            ])>
            {{ __('Type') }}
        </div>

        <div
            @class(['truncate', 
                'col-span-2' => \Laravel\Pennant\Feature::active(Flag::editDocumentVisibility()),
                'col-span-3' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility())
                ])>
            {{ __('Project') }}
        </div>

        @feature(Flag::editDocumentVisibility())
        <div class="justify-start  col-span-2">
            {{ __('Access') }}
        </div>
        @endfeature
        
    </div>
    @forelse ($documents as $document)
        <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
            
            <div
                @class([ 
                'flex',
                'gap-2',
                'items-center',
                'col-span-7' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility()),
                'col-span-5' => \Laravel\Pennant\Feature::active(Flag::editDocumentVisibility()),
                ]) 
            >
                <x-codicon-file-pdf class="text-gray-400 h-7 w-7 shrink-0" />
                
                <a href="{{ route('documents.show', $document) }}" class=" block font-bold truncate group-hover:text-blue-800">
                    <span class="z-10 absolute inset-0"></span>{{ $document->title }}
                </a>
            </div>

            <div
                @class([ 
                'col-span-2' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility())
                ])>
                @if ($document->type)
                    <span class="truncate inline-block text-xs px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900">{{ $document->type->name }}</span>
                @endif
            </div>

            <div
            @class(['truncate', 
                'col-span-2' => \Laravel\Pennant\Feature::active(Flag::editDocumentVisibility()),
                'col-span-3' => \Laravel\Pennant\Feature::inactive(Flag::editDocumentVisibility())
                ])>
                @if ($document->project)
                    <span class="truncate whitespace-nowrap text-sm">
                    {{ $document->project->title }}
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
