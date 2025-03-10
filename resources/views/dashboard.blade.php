<x-app-layout>
    

    <div class="py-12 px-4 sm:px-6 lg:px-8 space-y-12">
        
        <div class="space-y-3">

            <p class="font-bold text-lg"><a href="{{ route('documents.library') }}">{{ __('Documents') }} →</a></p>

            <div class="flex items-center justify-between">

                <div>
                    {{ __('Recently modified') }}
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
                    {{ __('Recently asked') }}
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
