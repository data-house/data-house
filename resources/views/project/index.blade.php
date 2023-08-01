<x-app-layout>
    <x-slot name="title">
        {{ __('Project database') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Project database')">

            <x-slot:actions>

            </x-slot>

            {{-- @include('library-navigation-menu') --}}
        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="" method="get">
                    <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search project database...') }}" />
                </form>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                @forelse ($projects as $project)
                    <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
            
                        <p class="inline text-xs px-2 py-1 rounded bg-lime-100 text-lime-900">
                            {{ $project->type->name }}
                        </p>

                        <a href="{{ route('projects.show', $project) }}" class="block font-bold group-hover:text-blue-800">
                            <span class="z-10 absolute inset-0"></span>{{ $project->title }}
                        </a>

                        @foreach ($project->topics as $topic)
                            <span class="flex gap-2 items-center text-xs px-2 py-1 rounded-xl bg-gray-100 text-gray-900">
                                <x-heroicon-o-hashtag class="w-4 h-4" />
                                {{ $topic }}
                            </span>
                        @endforeach

                        <div class="space-x-1 text-sm">
                            <span>{{ $project->countries()->pluck('value')->join(', ') }}</span>
                            <span>/</span>
                            <span>{{ $project->regions()->join(', ') }}</span>
                        </div>

                    </div>
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No projects fetched from the projectd database.') }}</p>
                    </div>
                @endforelse
            
            </div>
            

            <div class="mt-2">{{ $projects?->links() }}</div>
        </div>
    </div>
</x-app-layout>
