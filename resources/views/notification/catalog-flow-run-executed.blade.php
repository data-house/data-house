<div class="prose prose-sm">
    <p>
        @if ($notification['data']['flow'] ?? false && $notification['data']['status'] ?? false)
            {{ __(':flow :status for :document' , ['flow' => $notification['data']['flow'], 'status' => $notification['data']['status'], 'document' => $notification['data']['document_name']]) }}
        @endif
    </p>

    <p>
        @if ($notification['data']['catalog_id'] ?? false)
            <a href="{{ route('catalogs.show', $notification['data']['catalog_id'])}}">{{ __('Open catalog') }}</a>
        @endif
    </p>

    
</div>