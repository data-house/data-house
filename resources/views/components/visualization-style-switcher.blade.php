@props(['user'])

<div {{ $attributes->merge(['class' => 'flex gap-1 items-center'])}}>
    @php
        $visualizationPreference = $user->getPreference(\App\Models\Preference::VISUALIZATION_LAYOUT);

        $icons = [
            'grid' => 'squares-2x2',
            'list' => 'queue-list',
        ];

        $selectedStyle = '';
        $notSelectedStyle = '';
    @endphp

    @foreach (\App\Models\Preference::VISUALIZATION_LAYOUT->acceptableValues() as $style)
        
        <a href="{{ route('user-preferences', [
            'preference' => 'VISUALIZATION_LAYOUT',
            'value' => $style
        ])}}" class="p-1 rounded ring-1 hover:ring-indigo-600 hover:bg-indigo-100 focus:outline-none focus:ring-indigo-600 focus:bg-indigo-100 {{ (is_null($visualizationPreference) && $style==='grid') || (!is_null($visualizationPreference) && $visualizationPreference->value === $style) ? 'ring-indigo-400 text-indigo-600 bg-indigo-50' : ' ring-stone-400 '}}" title="{{ __('Change layout to :layout', ['layout' => $style]) }}">
        
            <x-dynamic-component :component="(is_null($visualizationPreference) && $style==='grid') || (!is_null($visualizationPreference) && $visualizationPreference->value === $style) ? 'heroicon-s-'.$icons[$style] : 'heroicon-o-'.$icons[$style]" class="w-6 h-6" />

        </a>
    @endforeach

    @error('preference')
        {{ $message }}
    @enderror

    @error('value')
        {{ $message }}
    @enderror
</div>
