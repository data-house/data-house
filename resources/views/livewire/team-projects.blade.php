<x-slideover :title="__(':Team projects', ['team' => $this->team->name])" description="{{ __('Here is a list of the projects your team is directly responsible for and the projects your team has been involved in.')}}">
    
    <h4 class="mt-6 text-base font-medium text-stone-900">{{ __('Managed projects') }}</h4>

    @forelse ($this->managed as $project)
        <div class="space-y-2 rounded overflow-hidden bg-white py-4 group relative">
            
            @feature(Flag::typeProjectFilter())
                <div class="flex justify-between">
                    @if ($project->type)
                        <p class="inline text-xs px-2 py-1 rounded bg-lime-100 text-lime-900">
                            {{ $project->type->label() }}
                        </p>
                    @endif
                </div>
            @endfeature

            <a href="{{ route('projects.show', $project) }}" class="block group-hover:text-blue-800">
                <span class="z-10 absolute inset-0"></span>{{ $project->title }}
            </a>

            <div class="flex flex-wrap gap-2">
                @foreach ($project->formattedTopics()->pluck('selected')->collapse() as $topic)
                    <span class="inline-flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-stone-100 text-stone-900">
                        <x-heroicon-o-hashtag class="w-4 h-4" />
                        {{ $topic['name'] }}
                    </span>
                @endforeach
            </div>

        </div>
    @empty
        <div class="col-span-3">
            <p>{{ __('You\'re team is not responsible of any projects.') }}</p>
        </div>
    @endforelse

    <x-section-border />

    <h4 class="text-base font-medium text-stone-900">{{ __('Involved projects') }}</h4>

    @forelse ($this->contributing as $project)
        <div class="space-y-2 rounded overflow-hidden bg-white py-4 group relative">
            
            @feature(Flag::typeProjectFilter())
                <div class="flex justify-between">
                    @if ($project->type)
                        <p class="inline text-xs px-2 py-1 rounded bg-lime-100 text-lime-900">
                            {{ $project->type->label() }}
                        </p>
                    @endif
                </div>
            @endfeature

            <a href="{{ route('projects.show', $project) }}" class="block group-hover:text-blue-800">
                <span class="z-10 absolute inset-0"></span>{{ $project->title }}
            </a>

            <div class="flex flex-wrap gap-2">
                @foreach ($project->formattedTopics()->pluck('selected')->collapse() as $topic)
                    <span class="inline-flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-stone-100 text-stone-900">
                        <x-heroicon-o-hashtag class="w-4 h-4" />
                        {{ $topic['name'] }}
                    </span>
                @endforeach
            </div>

        </div>
    @empty
        <div class="col-span-3">
            <p>{{ __('You\'re team is not responsible of any projects.') }}</p>
        </div>
    @endforelse

</x-slideover>
