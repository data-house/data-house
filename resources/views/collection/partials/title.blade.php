<div class="col-span-6 sm:col-span-4">
    <x-label for="title" value="{{ __('Collection name') }}" />
    <x-input-error for="title" class="mt-2" />
    <x-input id="title" type="text" name="title" class="mt-1 block w-full" autocomplete="title" value="{{ old('title', optional($collection ?? null)->title) }}" />
</div>