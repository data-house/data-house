<div class="divide-y bg-lime-50" >

    @if ($question && $question->isPending())
        
        <x-question ::wire:poll.visible :id="$ref ?? null" :question="$question" />
    
    @endif
</div>
