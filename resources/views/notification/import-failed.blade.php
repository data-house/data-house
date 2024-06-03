<p class="prose prose-sm">
    @if ($notification['data']['map'] ?? false)
        <a href="{{ route('mappings.show', $notification['data']['map'])}}">{{ __('View mapping') }}</a>
    @endif
</p>