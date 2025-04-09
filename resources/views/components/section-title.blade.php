<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-medium text-stone-900">{{ $title }}</h3>

        <p class="mt-1 text-sm text-stone-600">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0 flex items-center gap-2">
        {{ $aside ?? '' }}
    </div>
</div>
