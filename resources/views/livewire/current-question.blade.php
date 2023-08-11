<div class="divide-y {{ ($question ?? false) && $question?->isPending() ? 'bg-lime-50' : 'bg-white' }}">

    @if ($question)
        
        <x-question :poll="true" :id="$ref ?? null" :collapsed="false" :question="$question" />
    
    @endif
    
</div>