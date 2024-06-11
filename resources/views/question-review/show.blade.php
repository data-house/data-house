<x-app-layout>
    <x-slot name="title">
        {{ __('Review question') }} - {{ $review->question->question }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                <a href="{{ route('question-reviews.index') }}" class="px-1 py-0.5 bg-blue-50 rounded text-base inline-flex items-center text-blue-700 underline hover:text-blue-800">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    {{ __('Review requests') }}
                </a>
                {{ __('Question review') }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">   
            
            <div class="grid grid-cols-3 gap-6">

                <div class="col-span-3 font-bold prose prose-stone prose-lg prose-pre:rounded-md prose-p:whitespace-pre-wrap prose-p:m-0 prose-p:break-words w-full flex-1 leading-6 prose-p:leading-7 prose-pre:bg-[#282c34] max-w-full relative">
                    {!! $question->formattedText() !!}
                </div>

                <div class="col-span-2 flex flex-col gap-4">
                    <div class="col-span-2 space-y-2">
                        
                        <div class="prose prose-stone prose-sm sm:prose-base prose-pre:rounded-md prose-p:whitespace-pre-wrap prose-p:break-words w-full flex-1 leading-6 prose-p:leading-7 prose-pre:bg-[#282c34] max-w-full">
                            {!! $question->toHtml() !!}
                        </div>

                        <div class="prose prose-stone prose-sm sm:prose-base prose-pre:rounded-md prose-p:whitespace-pre-wrap prose-p:break-words w-full flex-1 leading-6 prose-p:leading-7 prose-pre:bg-[#282c34] max-w-full space-x-2">
                            @if ($question->isSingle())
                                {{-- pages within questionable --}}
                                @foreach (($question->answer['references'] ?? []) as $item)
                                    <a target="_blank" href="{{ $question->questionable->viewerUrl($item['page_number']) }}" class="no-underline rounded-sm font-mono px-1 py-0.5 text-sm  ring-stone-300 ring-1 bg-stone-200 hover:bg-lime-300 focus:bg-lime-300 hover:ring-lime-400 focus:ring-lime-400">{{ __('page :number', ['number' => $item['page_number']]) }}</a>
                                @endforeach
                                
                            @else
                                {{-- pages and references of the id of the questionable --}}
                                {{-- TODO: think on how to show references --}}
                                {{-- @dump($question->answer['references'] ?? []) --}}
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-stone-600">
                            {{ __('Answer generation is powered by OpenAI. Please always review answers before use.') }}
                        </p>                
                    </div>
                    <div class="flex flex-col gap-4">

                        <div>
                            
                            @if ($question->isSingle())
                                <h2 class="flex items-center gap-2 font-semibold text-xl text-stone-800 leading-tight break-all">
                                    <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                                    <a target="_blank" href="{{ route('documents.show', $question->questionable )}}">{{ $question->questionable->title }}</a>
                                </h2>
                    
                            @elseif($question->isMultiple())
                                <h2 class="flex items-center gap-2 font-semibold text-xl text-stone-800 leading-tight break-all">
                                    <x-heroicon-o-archive-box class="text-gray-400 h-10 w-h-10" />
                                    <a target="_blank" href="{{ $question->questionable->url() }}">{{ $question->questionable->title }}</a>
                                </h2>
                    
                            @endif
                        </div>

                        <div class="grid grid-cols-5 gap-2">
                            <div>
                                <span class="block text-xs uppercase tracking-wider text-stone-700">{{ __('Asked on') }}</span>
                                <x-date :value="$question->created_at" />
                            </div>
                            <div>
                                <span class="block text-xs uppercase tracking-wider text-stone-700">{{ __('Asked by') }}</span>
                                <span>{{ $question->user?->name ?? __('Question bot') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs uppercase tracking-wider text-stone-700">{{ __('Language') }}</span>
                                <span>{{ $question->language ?? __('Not identified') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs uppercase tracking-wider text-stone-700">{{ __('Answer generation took') }}</span>
                                <span>{{ $question->execution_time ? trans_choice(':amount second|:amount seconds', round($question->execution_time / 1000), ['amount' => round($question->execution_time / 1000)]) : '...'  }}</span>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold">{{ __('Assignee reviews and comments') }}</h3>

                        @if ($current_user_review_missing)

                            <div class="p-4 bg-white rounded shadow-md">

                                <p class="font-bold mb-3">{{ __('Add your review') }}</p>

                                <form action="{{ route('question-reviews.review-feedbacks.store', $review) }}" method="post">

                                    @csrf
                                
                                    <div class="">
                                        <x-input-error for="rating" class="mt-2" />

                                        <div class="grid grid-cols-3 gap-6">

                                            <label for="rating-good" class="flex gap-2 items-start">
                                                <x-radio id="rating-good" class="mt-0.5" name="rating" value="{{ \App\Models\FeedbackVote::LIKE->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-thumb-up class="w-5 h-5" />

                                                        {{ __('Good') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('The  answer quality is good enough and the content is correct to be used as reference.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            
                                            <label for="rating-improvable" class="flex gap-2 items-start">
                                                <x-radio id="rating-improvable" class="mt-0.5" name="rating" value="{{ \App\Models\FeedbackVote::IMPROVABLE->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-raised class="w-5 h-5" />

                                                        {{ __('Improvable') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('The answer quality can be improved with some additional information and/or references.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            
                                            <label for="rating-poor" class="flex gap-2 items-start">
                                                <x-radio id="rating-poor" class="mt-0.5" name="rating" value="{{ \App\Models\FeedbackVote::DISLIKE->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-thumb-down class="w-5 h-5" />

                                                        {{ __('Poor') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('The quality of the answer is not sufficient as might contain not relevant information.') }}
                                                    </p>
                                                </div>
                                            </label>

                                        </div>

                                    </div>
                                    <div class="mt-4">
                                        <x-label for="comment" value="{{ __('Comment') }}" />
                                        <p class="text-stone-600 text-sm mt-1">{{ __('Add a comment about your rating and judment of this question. Comments are visible by the coordinator and the other assignees.') }}</p>
                                        <x-input-error for="comment" class="mt-2" />
                                        <x-textarea id="comment" name="comment" class="mt-1 block w-full" autocomplete="none">{{ old('comment') }}</x-textarea>
                                    </div>

                                    <div class="mt-4">
                                        <x-button type="submit">{{ __('Submit review') }}</x-button>
                                    </div>

                                </form>

                            </div>
                            
                        @endif



                        



                        @forelse ($review->feedbacks as $item)
                            <div class="py-4 px-2 bg-white border border-stone-200 rounded flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <x-user :user="$item->user" />
                                    
                                    &mdash;
                                    {{ $item->vote->label() }}
                                </div>

                                @if ($item->user->is(auth()->user()))
                                    <form action="{{ route('review-feedbacks.destroy', $item) }}" method="post">
                                        @csrf
                                        @method('DELETE')

                                        <x-small-button type="submit">{{ __('Remove') }}</x-small-button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="py-4 px-2 bg-white border border-red-200 rounded flex justify-between items-center text-stone-700">{{ __('Reviewers did not provide feedback yet.') }}</p>
                        @endforelse

                        
                        @if ($current_user_is_coordinator && $review->feedbacks->isNotEmpty())
                        
                            <div class="p-4 bg-white rounded shadow-md">

                                <h3 class="text-lg font-bold mb-2">{{ __('Complete the review') }}</h3>

                                <p class="text-sm mb-4 text-stone-700">{{ __('You are the coordinator of the review. You can now complete the review and close it using the evaluations provided by the experts.') }}</p>

                                <form action="{{ route('question-reviews.update', $review) }}" method="post">

                                    @csrf
                                    @method('PUT')
                                
                                    <div class="">
                                        <x-input-error for="evaluation" class="mt-2" />

                                        <div class="grid grid-cols-3 gap-6">

                                            <label for="evaluation-good" class="flex gap-2 items-start">
                                                <x-radio id="evaluation-good" class="mt-0.5" name="evaluation" :checked="old('evaluation') == \App\Models\ReviewEvaluationResult::APPROVED->value ? 'checked' : false" value="{{ \App\Models\ReviewEvaluationResult::APPROVED->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-thumb-up class="w-5 h-5" />

                                                        {{ __('Approve') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('The answer is good as is and approved by experts.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            
                                            <label for="evaluation-improvable" class="flex gap-2 items-start">
                                                <x-radio id="evaluation-improvable" class="mt-0.5" name="evaluation" :checked="old('evaluation') == \App\Models\ReviewEvaluationResult::CHANGES_APPLIED->value ? 'checked' : false"  value="{{ \App\Models\ReviewEvaluationResult::CHANGES_APPLIED->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-raised class="w-5 h-5" />

                                                        {{ __('Changes required') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('Changes are required before approval. Experts suggested a new version.') }}
                                                    </p>
                                                </div>
                                            </label>
                                            
                                            <label for="evaluation-poor" class="flex gap-2 items-start">
                                                <x-radio id="evaluation-poor" class="mt-0.5" name="evaluation" :checked="old('evaluation') == \App\Models\ReviewEvaluationResult::REJECTED->value ? 'checked' : false" value="{{ \App\Models\ReviewEvaluationResult::REJECTED->value }}" />
                                                
                                                <div>
                                                
                                                    <div class="ml-2 text-stone-900 font-medium flex items-center gap-1">
                                                    
                                                        <x-heroicon-o-hand-thumb-down class="w-5 h-5" />

                                                        {{ __('Rejected') }}

                                                    </div>

                                                    <p class="mt-1 text-sm text-stone-600 max-w-md">
                                                        {{ __('The quality of the answer is not sufficient and experts suggests to not use the answer.') }}
                                                    </p>
                                                </div>
                                            </label>

                                        </div>

                                    </div>
                                    <div class="mt-4">
                                        <x-label for="updated_answer" value="{{ __('Updated answer') }}" />
                                        <p class="text-stone-600 text-sm mt-1">{{ __('If changes are required to the answer write here the updated answer.') }}</p>
                                        <x-input-error for="updated_answer" class="mt-2" />
                                        <x-textarea id="updated_answer" name="updated_answer" class="mt-1 block w-full" autocomplete="none">{{ old('updated_answer') }}</x-textarea>
                                    </div>
                                    <div class="mt-4">
                                        <x-label for="remark" value="{{ __('Remark') }}" />
                                        <p class="text-stone-600 text-sm mt-1">{{ __('Add a final comment on the use of this answer. This final remarks are visible by whom requested the review.') }}</p>
                                        <x-input-error for="remark" class="mt-2" />
                                        <x-textarea id="remark" name="remark" class="mt-1 block w-full" autocomplete="none">{{ old('remark') }}</x-textarea>
                                    </div>

                                    <div class="mt-4">
                                        <x-button type="submit">{{ __('Complete the review') }}</x-button>
                                    </div>

                                </form>

                            </div>
                            
                        @endif

                        @if($review->isComplete())
                            <div class="py-4 px-2 bg-white border border-stone-200 rounded flex flex-col">
                                <div class="flex items-center gap-2 mb-4">
                                    {{ $review->evaluation_result->label() }}
                                </div>

                                <div class="prose">
                                    {{ str($review->remarks)->markdown()->toHtmlString() }}
                                </div>

                            </div>
                        @endif
                

                        <div class="pl-8">
                            <livewire:note-list :resource="$review" />
                        </div>

                        
                    </div>
                
                </div>
                <div>
                    <livewire:select-question-review-assignee :review="$review" />

                    <x-section-border />

                    <livewire:question-review.select-question-review-coordinator :review="$review" />
                    

                    <x-section-border />
                    
                    <div class="space-y-3">
                        <h4 class="font-bold text-stone-700">{{ __('Request by') }}</h4>
                        
                        <p>
                            <x-user :user="$review->requestor" />
                        </p>
                    </div>

                    <x-section-border />
                    
                    <div class="space-y-3">
                        <h4 class="font-bold text-stone-700">{{ __('Review status') }}</h4>
                        
                        <p>
                            {{ $review->statusLabel() }}
                        </p>
                    </div>

                    <x-section-border />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
