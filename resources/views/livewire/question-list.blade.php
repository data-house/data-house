<div class="">
    <div class="max-w-4xl mx-auto mb-2">
        <h3 class="text-lg font-semibold">{{ __('Your questions') }}</h3>
    </div>
    
    <div class="divide-y bg-white">
        @forelse ($userQuestions as $question)
        
        <x-question :question="$question" />

        @empty

            <div class="text-stone-600 max-w-4xl mx-auto py-4">{{ __('You didn\'t aks a question so far.') }}</div>
        
        @endforelse
    </div>

    <div class="h-10"></div>

    <div class="max-w-4xl mx-auto mb-2">
        <h3 class="text-lg font-semibold">{{ __('Asked by other users and/or teammates') }}</h3>
    </div>

    <div class="divide-y bg-white">
        @forelse ($otherQuestions as $question)
        
        <x-question :question="$question" />

        @empty

            <div class="text-stone-600 max-w-4xl mx-auto py-4">{{ $userQuestions->isNotEmpty() ? __('You\'re the first to question this document.') : __('No one else has asked questions yet.') }}</div>
        
        @endforelse
    </div>
</div>
