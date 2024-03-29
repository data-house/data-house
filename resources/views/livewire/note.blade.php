<div class="">
    <div class="prose">
        {{ str($note->content)->markdown()->toHtmlString() }}
    </div>
    <div class="flex text-xs mt-1 justify-between">
        <p>{{ $note->created_at }}</p>

        <button type="button" class="underline" wire:click="remove">{{ __('Delete') }} </button>
    </div>

</div>
