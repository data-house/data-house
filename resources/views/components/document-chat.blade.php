@props(['document'])

<div {{ $attributes->merge(['class' => '']) }} id="chat">
@feature(Flag::questionWithAI())
    <div class="flex justify-between">
        <p class="mb-2 text-sm text-lime-700 flex items-center gap-1">
            <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />{{ __('Explore document\'s content by asking questions...') }}
        </p>
    </div>

    <div class="space-y-4">
        
        @can('create', \App\Models\Question::class)

            <div class="px-3 md:py-4 py-2.5 group transition-opacity message bg-stone-50">
                <div class="flex items-start max-w-4xl mx-auto space-x-3">
                    
                    <livewire:question-input :document="$document" />
                    
                </div>
                
            </div>
            <div>
                {{-- Show the last question and answer as given by current user --}}
                <livewire:current-question :document="$document" />
            </div>

        @endcan

        <div class="h-10"></div>
        
        <livewire:question-list :document="$document" />

    </div>
@endfeature
</div>
