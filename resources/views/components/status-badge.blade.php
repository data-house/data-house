@props(['status', 'size' => 'text-xs', 'style' => null ])

@php
    $styleClass = match ($style ?? (method_exists($status, 'style') ? $status->style() : null)) {
        'success' => 'bg-green-50 text-green-700 ring-green-600/20',
        'pending' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
        'warning' => 'bg-yellow-50 text-yellow-800 ring-yellow-600/20',
        'failure' => 'bg-red-50 text-red-700 ring-red-600/10',
        'cancel' => 'bg-pink-50 text-pink-700 ring-pink-700/10',
        default => ' bg-gray-50 text-gray-600 ring-gray-500/10',
    }
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2 py-1 $size font-medium ring-1 ring-inset"] )->merge(['class' => $styleClass])}}>
{{ method_exists($status, 'label') ? $status->label() : $status->name }}
</span>
