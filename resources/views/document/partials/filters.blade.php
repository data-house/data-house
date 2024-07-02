
<div class="grid auto-rows-min grid-cols-1 md:col-span-4 md:grid-cols-4 gap-4 md:gap-6">

    @if (!empty($sorting))
        <input type="hidden" name="sort" value="{{ $sorting }}">
    @endif

    <fieldset class="">
        <legend class="block font-medium">{{ __('Stars') }}</legend>
        <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">

            <div class="flex items-center text-base sm:text-sm">
                <input id="starred-{{ 'me' }}" name="starred" value="{{ 'me' }}" @checked($filters['stars'] ?? false) type="checkbox" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="starred-{{ 'me' }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ __('Only my starred') }}</label>
            </div>
        </div>
    </fieldset>

    @foreach ($search_collections as $scheme => $concepts)
        <div class="md:col-span-3 ">
            <p class="block font-medium">{{ $scheme }}</p>
            <div class="flex flex-row flex-wrap gap-4 pt-6 sm:pt-4 max-h-72  overflow-y-auto">
            @foreach ($concepts as $concept)

                <div class="flex items-center text-base sm:text-sm shrink-0">
                <input id="lc-{{ $concept->getKey() }}" name="library_collections[]" value="{{ $concept->getKey() }}" type="checkbox" @checked(in_array($concept->getKey(), $filters['library_collections'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="lc-{{ $concept->getKey() }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $concept->title }}</label>
                </div>
            @endforeach
            </div>
        </div>
    @endforeach

    @feature(Flag::sourceDocumentFilter())
        <fieldset class="">
            <legend class="block font-medium">{{ __('Source') }}</legend>
            <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">

                <div class="flex items-center text-base sm:text-sm">
                    <input id="source-{{ 'all-teams' }}" name="source" value="{{ 'all-teams' }}" @checked(($filters['source'] ?? []) === 'all-teams') type="radio" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="source-{{ 'all-teams' }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ __('All Teams') }}</label>
                </div>

                <div class="flex items-center text-base sm:text-sm">
                    <input id="source-{{ 'current-team' }}" name="source" value="{{ 'current-team' }}" @checked(($filters['source'] ?? []) === 'current-team') type="radio" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="source-{{ 'current-team' }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ __('Current Team') }}</label>
                </div>
            </div>
        </fieldset>
    @endfeature
</div>

<fieldset class="">
    <legend class="block font-medium">{{ __('Format') }}</legend>
    <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
    @foreach ($facets['format'] as $item)
        <div class="flex items-center text-base sm:text-sm">
        <input id="format-{{ $item }}" name="format[]" value="{{ $item }}" @checked(in_array($item, $filters['format'] ?? [])) type="checkbox" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <label for="format-{{ $item }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
        </div>
    @endforeach
    </div>
</fieldset>
        
@feature(Flag::typeDocumentFilter())
    <fieldset>
        <legend class="block font-medium">{{ __('Type') }}</legend>
        <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
        @foreach ($facets['type'] as $item)
            <div class="flex items-center text-base sm:text-sm">
            <input id="type-{{ $item->name }}" name="type[]" value="{{ $item->name }}" @checked(in_array($item->name, $filters['type'] ?? [])) type="checkbox" class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="type-{{ $item->name }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item->name }}</label>
            </div>
        @endforeach
        </div>
    </fieldset>
@endfeature

{{-- <fieldset>
    <legend class="block font-medium">{{ __('Area') }}</legend>
    <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
        @foreach ($facets['topic'] as $topicKey => $topic)
            <div class="flex items-center text-base sm:text-sm">
            <input id="topic-{{ $topicKey }}" name="project_topics[]" value="{{ $topicKey }}" type="checkbox" @checked(in_array($topicKey, $filters['project_topics'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="topic-{{ $topicKey }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $topic }}</label>
            </div>
        @endforeach
    </div>
</fieldset> --}}

@foreach ($search_topics as $scheme => $concepts)
    <div class="">
        <p class="block font-medium">{{ $scheme }}</p>
        <div class="flex flex-col gap-4 pt-6 sm:pt-4 max-h-72  overflow-y-auto">
        @foreach ($concepts as $concept)

            <div class="flex items-center text-base sm:text-sm">
            <input id="topic-{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" name="project_topics[]" value="{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" type="checkbox" @checked(in_array($concept['id'] ?? str($concept['name'])->slug()->toString(), $filters['project_topics'] ?? []) || in_array($concept['name'], $filters['project_topics'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="topic-{{ $concept['id'] ?? str($concept['name'])->slug()->toString() }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $concept['name'] }}</label>
            </div>
        @endforeach
        </div>
    </div>
@endforeach


        
<fieldset>
    <legend class="block font-medium">{{ __('Country') }}</legend>
    <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
    @foreach ($facets['countries'] as $item)
        <div class="flex items-center text-base sm:text-sm">
        <input id="countries-{{ str($item)->slug()->toString() }}" name="project_countries[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['project_countries'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <label for="countries-{{ str($item)->slug()->toString() }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
        </div>
    @endforeach
    
    </div>
</fieldset>

<fieldset>
    <legend class="block font-medium">{{ __('Region') }}</legend>
    <div class="space-y-6 pt-6 sm:space-y-4 sm:pt-4 max-h-72 overflow-y-auto">
    @foreach ($facets['regions'] as $item)
        <div class="flex items-center text-base sm:text-sm">
        <input id="region-{{ $item }}" name="project_region[]" value="{{ $item }}" type="checkbox" @checked(in_array($item, $filters['project_region'] ?? [])) class="h-4 w-4 flex-shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <label for="region-{{ $item }}" class="ml-3 min-w-0 flex-1 text-gray-600">{{ $item }}</label>
        </div>
    @endforeach
    </div>
</fieldset>