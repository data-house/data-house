@props(['project'])

<div {{ $attributes }}>
    @can('view', $project)
        <div class="space-y-1">
            <div class="prose">
                <a href="{{ route('projects.show', $project) }}">{{ $project?->title }}</a>
            </div>

            <div class="flex flex-wrap gap-2">

                @php
                    $countries = $project->countries();

                    $countriesPreview = $countries->take(5);
                @endphp

                @foreach ($countriesPreview as $country)
                    <a title="{{ __('Explore projects in :value', ['value' => $country->name]) }}" href="{{ route('projects.index', ['countries' => [$country->name]])}}" class="inline-flex gap-1 items-center text-xs px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                        <x-dynamic-component :component="$country->icon" class="w-4 h-4 text-gray-700 group-hover:text-indigo-600" />
                        {{ $country->name }}
                    </a>
                @endforeach

                @if ($countries->count() - 5 > 0)
                    <span class="text-stone-600 text-sm">{{ trans_choice('and :count other|and :count others', $countries->count() - 5,  ['count' => $countries->count() - 5]) }}</span>
                @endif
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($project->formattedTopics()->pluck('selected')->collapse() as $topic)
                    <a title="{{ __('Explore documents connected to projects in :value', ['value' => $topic['name']]) }}"
                    href="{{ route('documents.library', ['project_topics' => [$topic['id']]])}}"
                    class="inline-flex gap-1 items-center text-xs px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                        <x-heroicon-o-hashtag class="w-3 h-3 text-gray-700 group-hover:text-indigo-600" />
                        {{ $topic['name'] }}
                    </a>
                @endforeach
            </div>
        
            
        </div>
    @else
        <p class="prose prose-sm">{{ __('You are not allowed to see project details.') }}</p>
    @endcan
</div>