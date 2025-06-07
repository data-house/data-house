@props(['catalogs'])

<div {{ $attributes->merge(['class' => 'grid sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4'])}}>
    @forelse ($catalogs as $catalog)
        <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
            <div class="flex gap-2 justify-between">
                <x-heroicon-o-table-cells class="size-6 shrink-0 text-stone-500" />
                <x-document-visibility-badge :value="$catalog->visibility" />
            </div>

            <a wire:navigate href="{{ route('catalogs.show', $catalog) }}" class="block font-bold truncate group-hover:text-blue-800">
                <span class="z-10 absolute inset-0"></span>{{ $catalog->title }}
            </a>

            <p class="text-sm text-stone-500 truncate line-clamp-2">{{ $catalog->description }}</p>
        </div>
    @empty
        
        {{ $empty }}

    @endforelse

</div>
