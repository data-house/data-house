<p class="prose prose-sm">
    @if ($notification['data']['question'] ?? false)
        <a href="{{ route('questions.show', $notification['data']['question'])}}">{{ __('View question') }}</a>
    @endif
    @if ($notification['data']['review'] ?? false)
        <a href="{{ route('question-reviews.show', $notification['data']['review'])}}">{{ __('View review') }}</a>
    @endif
</p>