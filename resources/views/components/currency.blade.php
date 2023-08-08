@props(['value', 'currency' => '€'])

<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>
    @if (($slot ?? false) && !empty(trim($slot)))
        {{ $slot }}
    @else
        {{ (new \NumberFormatter(app()->currentLocale(), \NumberFormatter::DECIMAL))->format($value) }}
    @endif
    {{ $currency }}
</span>
