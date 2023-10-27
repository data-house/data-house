@props(['value'])

<span {{ $attributes->merge(['class' => 'inline-flex gap-1 text-xs items-center px-3 py-1 rounded-xl ring-0 ring-stone-300 bg-stone-100 text-stone-900']) }}>
    @if (($slot ?? false) && !empty(trim($slot)))
        {{ $slot }}
    @else
        <x-dynamic-component :component="$value?->icon() ?? 'heroicon-o-eye'" class="w-3 h-3 shrink-0" /> {{ $value?->label() ?? \App\Models\Visibility::TEAM->label() }}
    @endif
</span>
