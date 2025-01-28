
<div class="flex flex-col gap-8 sticky top-0">

    

    <div class="">        
        <ul class="flex flex-col gap-2 border-l border-stone-400/25">
            @foreach ($this->vocabularyTopConcepts as $concept)

                <x-skos-scheme-tree-item :concept="$concept" :selected-concept="$selectedConcept" />
            
            
            @endforeach
        </ul>

    </div>

    <div class="flex flex-col gap-3">

        <h3 class="text-xs font-medium tracking-widest text-gray-500 uppercase  ">Groups</h3>

        <ul class="flex flex-col gap-2 border-l border-stone-400/25">
            @foreach ($this->vocabulary->collections as $collection)
            
                <li class="-ml-px flex flex-col items-start gap-2" x-cloak x-data="{expand: false}">
                    <button x-on:click="expand = ! expand" class="sticky top-0 text-left text-sm inline-flex items-center gap-2 border-l border-transparent text-gray-700 hover:border-gray-950/25 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4">
                        <x-heroicon-o-archive-box class="w-5 h-5 text-gray-600" />
                        <span>{{$collection->pref_label}}</span>
                    </button>

                    <ul class="ml-6 flex flex-col gap-2 border-l border-stone-400/25" x-show="expand">

                        @foreach ($collection->concepts as $concept)
                        
                        <li class="-ml-px flex flex-col items-start gap-2">
                            <a class="text-left text-sm inline-block border-l border-transparent text-gray-700 hover:border-gray-950/25 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                            href="{{ route('vocabulary-concepts.show', $concept) }}"
                            >
                            {{$concept->pref_label}}
                        </a>
                        </li>
                        @endforeach
                    </ul>
                </li>
    
            @endforeach
        </ul>
    </div>

</div>