<x-app-layout>
    <x-slot name="title">
        {{ $question->question }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Question')">

            <x-slot:actions>
                
            </x-slot>

            @include('library-navigation-menu')
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">   
            
            <div class="grid grid-cols-3 gap-4">
                <div class=" col-span-2 space-y-2">

                    <div class="bg-white">
                        <livewire:question :question="$question" :poll="$question?->isPending() ?? false" />
                    </div>
                        
                    <p class="text-xs text-stone-600">
                        {{ __('Answer generation is powered by OpenAI. Please always review answers before use.') }}
                    </p>
                </div>

                <div class="flex flex-col gap-4">
                        
                    @if ($question->isPending() || $question->hasError())
                        <div class="">
                            @if ($question->isPending())
                                <span class="px-1 py-0.5 rounded-md bg-lime-100 border border-lime-400 text-lime-800">{{ __('Answering') }}</span>
                            @elseif ($question->hasError())
                                <span class="px-1 py-0.5 rounded-md bg-red-100 border border-red-400 text-red-800">{{ __('Error') }}</span>
                            @endif
                        </div>
                    @endif

                    @if ($question->isSingle())

                        <div class="p-2 shadow bg-white flex flex-col gap-1">
                            {{-- Document card if directly connected to document --}}
                            <div class="">
                                <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                            </div>

                            <h2 class="font-semibold text-xl text-stone-800 leading-tight break-all">
                                <a href="{{ route('documents.show', $question->questionable )}}">{{ $question->questionable->title }}</a>
                            </h2>

                            <div class="prose prose-green">
                                {!! \Illuminate\Support\Str::markdown($document->description ?? __('This document doesn\'t have an abstract.')) !!}
                            </div>
                            
                        </div>

                    @elseif($question->isMultiple())

                        <div class="p-2 shadow bg-white flex flex-col gap-1">
                            {{-- Collection card if directly connected to connection --}}
                            <div class="">
                                <x-heroicon-o-archive-box class="text-gray-400 h-10 w-h-10" />
                            </div>

                            <h2 class="font-semibold text-xl text-stone-800 leading-tight break-all">
                                <a href="{{ $question->questionable->url() }}">{{ $question->questionable->title }}</a>
                            </h2>
                            
                        </div>

                    @endif

                    @if ($question->ancestors->isNotEmpty())
                        
                        <div>
                            {{-- Connected questions --}}

                            @foreach ($question->ancestors as $linkedQuestion)
                                <div class="p-2 shadow bg-white flex flex-col gap-1">
                                    <div class="">
                                        <x-heroicon-o-sparkles class="text-gray-400 h-10 w-h-10" />
                                    </div>

                                    <h2 class="font-semibold text-xl text-stone-800 leading-tight break-all">
                                        <a href="{{ route('questions.show', $linkedQuestion) }}">{{ $linkedQuestion->question }}</a>
                                    </h2>
                                    
                                </div>
                                
                            @endforeach

                        </div>
                    @endif
                
                    <div class="flex flex-col gap-2">

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

                </div>
                
            </div>
        </div>

        @if($question->isMultiple())
            <livewire:child-questions :question="$question" />
        @endif
    </div>
</x-app-layout>
