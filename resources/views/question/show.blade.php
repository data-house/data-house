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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">   
            
            <div>
                <p class="flex items-center gap-4">
                    
                    @if ($question->isSingle())
                        <x-heroicon-o-document class="w-4 h-4" title="{{ __('Question on a single document') }}" />
                    @elseif ($question->isMultiple())
                        <x-heroicon-o-archive-box class="w-4 h-4" title="{{ __('Question over a collection of documents') }}" />
                    @endif
                    
                    @if ($question->isPending())
                        <span class="px-1 py-0.5 rounded-md bg-lime-100 border border-lime-400 text-lime-800">{{ __('Answering') }}</span>
                    @elseif ($question->hasError())
                        <span class="px-1 py-0.5 rounded-md bg-red-100 border border-red-400 text-red-800">{{ __('Error') }}</span>
                    @endif
            
                    <x-date :value="$question->created_at" />
                    <span>{{ $question->user?->name }}</span>
                    @if ($question->language)
                        <span>{{ $question->language }}</span>
                    @endif
                    <span title="{{ __('Time required to generate the answer') }}">{{ trans_choice(':amount second|:amount seconds', round($question->execution_time / 1000), ['amount' => round($question->execution_time / 1000)])  }}</span>
                </p>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                <div class=" col-span-2 space-y-2">

                    <div class="bg-white">
                        <livewire:question :question="$question" :poll="$question?->isPending() ?? false" />
                    </div>
                        
                    <p class="text-xs text-stone-600">
                        {{ __('Answer generation is powered by OpenAI. Please always review answers before use.') }}
                    </p>
                </div>

                <div class="space-y-2">
                    
                    @if ($question->isSingle())
                    
                        <h2 class="font-semibold text-xl text-stone-800 leading-tight break-all">
                            <a href="{{ route('documents.show', $question->questionable )}}">{{ $question->questionable->title }}</a>
                        </h2>
                        <div class="flex gap-2">
                            @can('view', $question->questionable)
                                <x-button-link href="{{ $question->questionable->viewerUrl() }}" target="_blank">
                                    {{ __('Open Document') }}
                                </x-button-link>
                            @endcan
                        </div>

                        <div class="aspect-video bg-white flex items-center justify-center">
                            {{-- Space for the thumbnail --}}
                            <x-codicon-file-pdf class="text-gray-400 h-10 w-h-10" />
                        </div>

                        <div class="space-y-2">
                            <h4 class="font-bold">{{ __('Details') }}</h4>
                            
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('File type') }}</span>{{ $question->questionable->mime }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Uploaded by') }}</span>{{ $question->questionable->uploader->name }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Team') }}</span>{{ $question->questionable->team?->name }}</p>
                            <p><span class="text-xs uppercase block text-stone-700">{{ __('Language') }}</span>{{ $question->questionable->languages?->join(',') }}</p>
                            
                        </div>

                    @elseif($question->isMultiple())
                        <h2 class="font-semibold text-xl text-stone-800 leading-tight break-all">
                            <a href="{{ $question->questionable->url() }}">{{ $question->questionable->title }}</a>
                        </h2>
                    @endif

                </div>
                
            </div>
        </div>

        @if($question->isMultiple())
            <livewire:child-questions :question="$question" />
        @endif
    </div>
</x-app-layout>
