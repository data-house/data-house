<div class="mt-10 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-2" 
@if ($question?->isPending())
    wire:poll.visible.10000ms
@endif >
    @include('question.partials.children')
</div>