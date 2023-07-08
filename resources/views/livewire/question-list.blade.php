<div class="divide-y bg-white">
    
    @foreach ($questions as $question)

        <x-question :question="$question" />
    
    @endforeach

</div>
