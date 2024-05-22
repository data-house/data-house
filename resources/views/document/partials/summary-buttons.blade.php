<div class="mb-4 flex gap-4 justify-start items-start">
    @can('update', $document)

        <x-small-button type="button" onclick="Livewire.dispatch(
            'openSlideover', { 
                component: 'summary-editor', 
                arguments: { 
                    document: '{{ $document->ulid }}'
                }
            })" >
            <x-heroicon-s-pencil class="text-stone-600 h-4 w-4" />
            {{ __('Write a summary')}}
        </x-small-button>

        @summary()
            <livewire:document-summary-button :document="$document" />
        @endsummary

    @endcan
</div>