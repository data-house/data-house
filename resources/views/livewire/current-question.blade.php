<div class="divide-y {{ ($question ?? false) && $question?->isPending() ? 'bg-lime-50' : 'bg-white' }}">

    @if ($question)
        
        <x-question :poll="$question?->isPending() ?? false" :id="$ref ?? $question?->uuid" :collapsed="false" :question="$question" />
    
    @endif
    
</div>