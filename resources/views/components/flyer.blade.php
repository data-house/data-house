@props(['align' => 'right', 'width' => null, 'state' => '{ open: false }'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    'none', 'false' => '',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-2/3 sm:w-48',
    '60' => 'w-2/3 sm:w-60',
    '80' => 'w-2/3 sm:w-80',
    'half' => 'w-2/3 md:w-[50vw]',
    'third' => 'w-2/3 md:w-[30vw]',
    default => 'w-2/3 sm:w-[50vw] md:w-80 ',
};
@endphp

<div class="md:relative" x-data="{{ $state }}" x-trap="open" x-on:closedropdown.window="open = false" @click.away="open = false" @close.stop="open = false" @keydown.escape="open = false">
    <button @click="open = ! open" class="-mr-3 h-9 inline-flex gap-1 items-center px-3 py-2 border border-transparent text-sm leading-4 rounded-md text-stone-600 hover:text-stone-800 hover:bg-stone-200 focus:outline-none focus:bg-stone-200 active:bg-stone-200 transition duration-150 ease-in-out">
        {{ $trigger }}
    </button>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0"
            x-transition:enter-end="transform opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100"
            x-transition:leave-end="transform opacity-0"
            class="fixed top-0 bg-white inset-y-0 z-50 {{ $width }} rounded-l-lg shadow-xl border border-stone-400/40 {{ $alignmentClasses }}"
            style="display: none;">
        <div class="rounded-l-lg ring-opacity-5 bg-white p-3 flex flex-col">

            @if (isset($heading))
                <div class="mb-3 flex justify-between">
                    <div>
                        {{ $heading }}
                    </div>

                    <button aria-label="{{ __('Close') }}" @click="open = false" class="h-9 inline-flex gap-1 items-center p-2 border border-transparent text-sm leading-4 rounded-md text-stone-600 hover:text-stone-800 hover:bg-stone-200 focus:outline-none focus:bg-stone-200 active:bg-stone-200 transition duration-150 ease-in-out">
                        <x-heroicon-o-x-mark class="w-6 h-6 text-stone-600" />
                    </button>
                </div>
            @endif

            {{ $content }}
        </div>
    </div>
</div>
