@props(['catalogs'])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 pt-6 sm:pt-0'])}}>
    @if ($catalogs->isNotEmpty())
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
                
                {{ __('Catalog') }}
            </div>

            <div
                @class([ 
                'hidden md:block md:col-span-3', 
                ])>
                {{ __('Description') }}
            </div>
            
            <div class="hidden sm:block sm:col-span-2">
                {{ __('Updated on') }}
            </div>
            
        </div>
    @endif

    @forelse ($catalogs as $catalog)
        <div class="grid grid-rows-2 sm:grid-rows-1 sm:grid-cols-12 gap-2 sm:items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
            
            <div
                @class([ 
                'flex',
                'gap-2',
                'items-center',
                'sm:col-span-7 md:col-span-6',
                ]) 
            >
                <x-heroicon-o-table-cells class="size-7 shrink-0 text-stone-500" />
                
                <a wire:navigate href="{{ route('catalogs.show', $catalog) }}" class="min-w-0 block font-bold truncate group-hover:text-blue-800">
                    <span class="z-10 absolute inset-0"></span>{{ $catalog->title }}
                </a>

                <x-document-visibility-badge :value="$catalog->visibility" />
            </div>

            <div
                @class([ 
                'hidden md:block md:col-span-3 truncate text-xs'
                ])>
                
                {{ $catalog->description }}
                
            </div>

            <div class="sm:col-span-2">
                {{ $catalog->updated_at?->toDateString() }}
            </div>
        </div>
    @empty
        
        {{ $empty }}
        
    @endforelse

</div>
