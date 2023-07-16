@props(['question'])

@php
$classes = ($question?->isPending() ?? false)
            ? 'bg-gradient-to-br from-lime-200/40 from-10% to-stone-50 to-30%'
            : 'bg-stone-50';

if($question->status === \App\Models\QuestionStatus::ERROR){
    $classes = 'bg-gradient-to-br from-red-200/40 from-10% to-stone-50 to-30%';
}
@endphp

<div {{ $attributes->merge(['class' => 'relative flex flex-col gap-2 px-3 md:py-4 py-2.5 rounded shadow group transition-opacity focus:ring-lime-500 focus-within:ring-lime-500 ' . $classes]) }}>
    <p class="flex items-center gap-4 text-sm">
        @if ($question->isPending())
            <span class="px-1 py-0.5 rounded-md bg-lime-100 border border-lime-400 text-lime-800">{{ __('Answering') }}</span>
        @elseif ($question->hasError())
            <span class="px-1 py-0.5 rounded-md bg-red-100 border border-red-400 text-red-800">{{ __('Error') }}</span>
        @endif

        <x-date :value="$question->created_at" />
        <span>{{ $question->user?->name }}</span>
    </p>

    <a href="{{ route('questions.show', $question) }}" class="block font-bold truncate group-hover:text-blue-800">
        <span class="z-10 absolute inset-0"></span>{{ $question->question }}
    </a>

    <p class="text-xs inline-block rounded px-1 py-0.5 bg-white truncate">
        {{ $question->questionable?->title }}
    </p>
    
    <p class="line-clamp-3">{{ $question->toText() }}</p>
    
    
    <div class="flex flex-row gap-2 items-center" data-state="closed">
        <x-copy-clipboard-button :value="$question->url()" title="{{ __('Copy link to question') }}" class="opacity-0 cursor-default group-hover:opacity-100 group-focus:opacity-100  group-focus-within:opacity-100">
            <x-slot:icon><x-heroicon-m-link class="w-5 h-5" /></x-slot>
            {{ __('Link') }}
        </x-copy-clipboard-button>
        <x-copy-clipboard-button :value="$question->question" title="{{ __('Copy question text') }}" class="opacity-0 cursor-default group-hover:opacity-100 group-focus:opacity-100  group-focus-within:opacity-100">
            {{ __('Copy') }}
        </x-copy-clipboard-button>
    </div>
</div>