<x-app-layout>
    

    <div class="p-4 sm:p-6 lg:py-10 lg:px-8 space-y-10">
        
        <div class="space-y-3">

            <p class="font-bold text-lg"><a href="{{ route('documents.library') }}">{{ __('Documents') }} →</a></p>

            <div class="flex items-center justify-between">

                <div>
                    <div class="block inline-flex p-1 rounded-lg bg-zinc-800/5 dark:bg-white/10 h-8 py-[3px] px-[3px] -my-px h-[calc(2rem+2px)]" data-flux-tabs="" role="tablist">
                        <a href="{{ route('dashboard') }}" class="flex whitespace-nowrap flex-1 justify-center items-center gap-2 rounded-md shadow-xs text-sm font-medium  hover:text-zinc-800 text-zinc-800 bg-white px-3" data-flux-tab="data-flux-tab" data-selected="" data-active="" tabindex="0" aria-selected="true" role="tab">
                            {{ __('Recently modified') }}
                        </a>
                    </div>
                </div>

                <x-visualization-style-switcher :user="auth()->user()" class="pl-4" />

            </div>


            @php
                $visualizationStyle = 'document-' . (auth()->user()->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT)?->value ?? 'grid');
            @endphp

            <x-dynamic-component :component="$visualizationStyle" class="" :documents="$documents" empty="{{ __('No documents recently added, modified or viewed') }}" />


            <p class="text-right"><a href="{{ route('documents.library') }}">{{ __('View all documents') }} →</a></p>
        </div>
        
        
        @can('viewAny', \App\Models\Question::class)


        <div class="space-y-3">

            <p class="font-bold text-lg"><a href="{{ route('questions.index') }}">{{ __('Questions') }} →</a></p>

            <div class="flex items-center justify-between">

                <div>
                    <div class="block inline-flex p-1 rounded-lg bg-zinc-800/5 dark:bg-white/10 h-8 py-[3px] px-[3px] -my-px h-[calc(2rem+2px)]" data-flux-tabs="" role="tablist">
                        <a href="{{ route('dashboard') }}" class="flex whitespace-nowrap flex-1 justify-center items-center gap-2 rounded-md shadow-xs text-sm font-medium  hover:text-zinc-800 text-zinc-800 bg-white px-3" data-flux-tab="data-flux-tab" data-selected="" data-active="" tabindex="0" aria-selected="true" role="tab">
                            {{ __('Recently asked') }}
                        </a>
                    </div>
                </div>

            </div>


            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse ($questions as $question)
                <x-question-card :question="$question" />
            @empty
                <div class="sm:col-span-2 md:col-span-3">
                    <p>{{ __('No recent questions in the library.') }}</p>
                </div>
            @endforelse
            </div>


            <p class="text-right"><a href="{{ route('questions.index') }}">{{ __('View all questions') }} →</a></p>
        </div>

        <div class="">






            
        </div>

        @endcan


    </div>
</x-app-layout>
