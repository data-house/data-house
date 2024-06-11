<p class="prose prose-sm">
    @if ($notification['data']['review'] ?? false)
        <a href="{{ route('question-reviews.show', $notification['data']['review'])}}">{{ __('View review') }}</a>
    @endif
</p>