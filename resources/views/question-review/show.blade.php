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
                
                        <x-section-border />

                        <livewire:note-list :resource="$review" />
                        
                        <div>
                            <form action="" method="post">

                            </form>
                        </div>
                        
                    </div>
                
                </div>
                <div>
                    <livewire:select-question-review-assignee :review="$review" />

                    <x-section-border />

                    <livewire:question-review.select-question-review-coordinator :review="$review" />
                    

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
