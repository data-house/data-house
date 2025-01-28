<x-slideover description="">

    <x-slot:title>
        <a href="{{ route('vocabularies.show', $this->vocabulary) }}">{{ $this->vocabulary->pref_label }}</a>
    </x-slot>

    <x-slot:aside>

        <a target="_blank" class="inline-flex items-center px-2 py-1 gap-2 bg-white border border-stone-300 rounded-md font-semibold text-xs text-stone-700 shadow-sm hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
        href="{{ route('vocabulary-concepts.show', $this->concept) }}">
            <x-heroicon-o-arrow-top-right-on-square class="size-4" />
            {{ __('Open')}}
        </a>
    </x-slot>

    <div class="flex flex-col gap-3">

    <h2 class="text-3xl font-bold sticky top-0 bg-white">
        <a href="{{ route('vocabulary-concepts.show', $this->concept) }}">{{ $this->concept->pref_label }}</a>
    </h2>


    <p>{{ $this->concept->definition }}</p>


    <p class="text-sm flex items-center flex-wrap gap-2 text-stone-600">

        <span class="sr-only">{{ __('Also written as') }}:</span>

        @if ($this->concept->notation)
            <code class="font-mono text-xs px-1 py-0.5 rounded ring-1 ring-stone-300  text-stone-900 bg-white">{{ $this->concept->notation }}</code>
            <span class="text-stone-400" aria-hidden="true">&middot;</span>
        @endif

        @foreach ($this->concept->alt_labels as $label)
            
            <span>{{ $label }}</span>

            @if (!$loop->last)
                <span class="text-stone-400" aria-hidden="true">&middot;</span>
            @endif
        @endforeach

    </p>

    <details open>

        <summary  class="mb-3 text-xs tracking-widest text-gray-500 uppercase  ">Broader concepts</summary>

        <ul class="flex flex-col gap-2 border-l border-stone-400/25">
            @foreach ($this->concept->broader as $concept)
            
            <li class="-ml-px flex flex-col items-start gap-2">
                <a class="text-left text-sm inline-block border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                    href="{{ route('vocabulary-concepts.show', $concept)}}">
                    {{$concept->pref_label}}
                </a>
            </li>
            
            @endforeach
        </ul>

    </details>

    <details>

        <summary class="mb-3 text-xs tracking-widest text-gray-500 uppercase  ">Narrower concepts</summary>

        <ul class="flex flex-col gap-2 border-l border-stone-400/25 mb-3">
            @foreach ($this->concept->narrower as $concept)
            
            <li class="-ml-px flex flex-col items-start gap-2">
                <a class="text-left text-sm inline-block border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                    href="{{ route('vocabulary-concepts.show', $concept)}}">
                    {{$concept->pref_label}}
                </a>
            </li>
            
            @endforeach
        </ul>

    </details>
    
    <div>

        <h4 class="mb-3 text-xs tracking-widest text-gray-500 uppercase  ">Concepts from other vocabularies</h4>

        <ul class="flex flex-col gap-2 border-l border-stone-400/25 mb-3">
            @foreach ($this->concept->mappingMatches as $concept)
            
            <li class="-ml-px flex flex-col items-start gap-2">
                <a class="text-left text-sm inline-block border-l border-transparent text-gray-700 hover:border-gray-950/60 hover:text-gray-950  aria-[current]:border-gray-950 aria-[current]:font-semibold aria-[current]:text-gray-950  pl-5 sm:pl-4"
                    href="{{ route('vocabulary-concepts.show', $concept)}}">
                    {{$concept->pref_label}}
                </a>
            </li>
            
            @endforeach
        </ul>

    </div>

</div>
    
</x-slideover>
