@props(['language'])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-1']) }}>
    @if ($language)
        <span class="text-xs uppercase font-mono inline-block px-2 py-1 bg-white border border-stone-700/10 rounded-sm shadow">{{ $language->value }}</span>

        {{ $language->toLanguageName() }}
    @else
    -
    @endif
</div>