<div class="divide-y bg-lime-50" wire:poll.visible="">

    @if ($question && $question->isPending())
        
        <x-question :id="$ref ?? null" :question="$question" />
    
    @endif
    
</div>