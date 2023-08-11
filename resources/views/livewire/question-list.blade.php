<div class="">

    @can('create', \App\Models\Question::class)
        <div class="max-w-4xl mx-auto mb-2">
            <h3 class="text-lg font-semibold">{{ __('Your questions') }}</h3>
        </div>
        
        <div class="divide-y bg-white">
            @forelse ($userQuestions as $question)
            
            <x-question collapsed="true" :question="$question" />

            @empty

                <div class="text-stone-600 max-w-4xl mx-auto py-4">{{ __('No questions yet.') }}</div>
            
            @endforelse
        </div>

        <div class="h-10"></div>
    @endcan

    <div class="max-w-4xl mx-auto mb-2">
        <h3 class="text-lg font-semibold">{{ __('Asked by other users and/or teammates') }}</h3>
    </div>

    <div class="divide-y bg-white">
        @forelse ($otherQuestions as $question)
        
        <x-question collapsed="true" :question="$question" />

        @empty

            <div class="text-stone-600 max-w-4xl mx-auto py-4">{{ __('No questions yet.') }}</div>
        
        @endforelse
    </div>
</div>
