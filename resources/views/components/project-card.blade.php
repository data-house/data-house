@props(['project'])

<div {{ $attributes }}>
    @can('view', $project)
        <div class="space-y-1">
            <div class="prose">
                <a href="{{ route('projects.show', $project) }}">{{ $project?->title }}</a>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($project?->countries() as $country)
                    <a title="{{ __('Explore documents connected to projects in :value', ['value' => $country->name]) }}" href="{{ route('documents.library', ['project_countries' => [$country->name]])}}" class="inline-flex gap-1 items-center text-xs px-2 py-1 rounded-xl bg-gray-200 text-gray-900 hover:bg-indigo-200 focus:bg-indigo-200 hover:text-indigo-800 focus:text-indigo-800 group">
                        <x-dynamic-component :component="$country->icon" class="w-4 h-4 text-gray-700 group-hover:text-indigo-600" />
                        {{ $country->name }}
                    </a>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($project->formattedTopics() as $topic)
                    <a title="{{ __('Explore documents connected to projects in :value', ['value' => $topic['name']]) }}"
                    href="{{ route('documents.library', ['topics' => [$topic['id']]])}}"
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