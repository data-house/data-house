@props(['document'])

<div {{ $attributes->merge(['class' => '']) }}>

    <p class="mb-2 text-sm text-lime-700 flex items-center gap-1">
        <x-heroicon-s-sparkles class="text-lime-500 h-6 w-6" />{{ __('Explore document\'s content by asking questions...') }}
    </p>

    
    

    <div class="divide-y bg-white">
        
        <livewire:question-list :document="$document" />


        <div class="px-3 md:py-4 py-2.5 group transition-opacity message bg-stone-50">
            <div class="flex items-start max-w-4xl mx-auto space-x-3">

                <livewire:question-input :document="$document" />

            </div>

        </div>

    </div>


        


</div>