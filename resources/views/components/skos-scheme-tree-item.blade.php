<li x-data="{expand: {{ $selectedConcept === ($concept->id ?? $concept->descendant_id) || $concept->children_ids?->contains($selectedConcept) ? 'true' : 'false'}}}"
    class="-ml-px grow border-l border-transparent hover:border-stone-400" 
    >
    <div {{ $selectedConcept === ($concept->id ?? $concept->descendant_id) || $concept->children_ids?->contains($selectedConcept) ? ' aria-current="true" ' : '' }}
    class="grid grid-cols-[32px_1fr] items-center text-gray-700 border-l border-transparent hover:bg-stone-200 aria-[current]:border-gray-950/25 hover:text-gray-950 aria-[current]:font-bold aria-[current]:border-gray-950 aria-[current]:text-gray-950">
        {{-- <div></div> --}}
        <div>
            @if ($concept->children->isNotEmpty())
                <button type="button" class="w-8 p-2 shrink-0 hover:bg-stone-300" x-on:click="expand = !expand">
                    <x-heroicon-o-chevron-right class="size-4" x-bind:class="{'transform rotate-90': expand }" />
                </button>
            @else
                <div class="w-8 shrink-0"></div>
            @endif
        </div>
        <a {{ $selectedConcept === ($concept->id ?? $concept->descendant_id) || $concept->children_ids?->contains($selectedConcept) ? ' aria-current="true" ' : '' }}
            class="text-left text-sm"
            href="{{ route('vocabulary-concepts.show', ['vocabulary_concept' => ($concept->id ?? $concept->descendant_id)]) }}"
            >
            {{$concept->pref_label}}
        </a>
    </div>


    <ul class="mt-2 ml-3.5 flex flex-col gap-1 border-l border-stone-400/25" x-cloak x-show="expand">

        @foreach ($concept->children as $item)

            <x-skos-scheme-tree-item :concept="$item" :selected-concept="$selectedConcept" />

        @endforeach
        
    </ul>
</li>