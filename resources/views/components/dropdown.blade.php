@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white', 'dropdownClasses' => '', 'state' => '{ open: false }'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    'none', 'false' => '',
    default => 'ltr:origin-top-right rtl:origin-top-left start-0 sm:end-0 sm:start-auto',
};

$width = match ($width) {
    '48' => 'w-full sm:w-48',
    '60' => 'w-full sm:w-60',
    '80' => 'w-full sm:w-80',
    'half' => 'w-full md:w-[50vw]',
    'third' => 'w-full md:w-[30vw]',
    default => 'w-full sm:w-48',
};
@endphp

<div class="md:relative" x-data="{{ $state }}" x-trap="open" x-on:closedropdown.window="open = false" @click.away="open = false" @close.stop="open = false" @keydown.escape="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg border border-stone-300/40 {{ $alignmentClasses }} {{ $dropdownClasses }}"
            style="display: none;">
        <div class="rounded-md ring-1 ring-black ring-opacity-5  {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
