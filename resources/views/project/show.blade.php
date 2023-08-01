<x-app-layout>
    <x-slot name="title">
        {{ $project->title }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ $project->title }}
            </h2>
            <div class="flex gap-2">

            </div>
        </div>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-3 gap-2">

                <div class="space-y-4">

                    <p class="inline px-2 py-1 rounded bg-lime-100 text-lime-900">
                        {{ $project->type->name }}
                    </p>

                    <p class="text-xs uppercase block text-stone-700">{{ __('Topics') }}</p>
                    <ul>
                        @foreach ($project->topics as $topic)
                            <li class="flex gap-2 items-center text-sm px-2 py-1 rounded-xl bg-gray-200 text-gray-900">
                                <x-heroicon-o-hashtag class="w-5 h-5" />
                                {{ $topic }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs uppercase block text-stone-700">{{ __('Countries') }}</p>
                    <div class="prose">
                        <ul>
                            @foreach ($project->countries()->pluck('value') as $country)
                                <li>{{ $country }}</li>
                            @endforeach 
                        </ul>
                    </div>
                </div>
                <div>
                    <p class="text-xs uppercase block text-stone-700">{{ __('Regions') }}</p>
                    <div class="prose">
                        <ul>
                            @foreach ($project->regions() as $region)
                                <li>{{ $region }}</li>
                            @endforeach 
                        </ul>
                    </div>
                </div>

            </div>

            <div class="h-10"></div>

            <div class="max-w-7xl mx-auto mb-2">
                <h3 class="text-lg font-semibold">{{ __('Project reports and documents') }}</h3>
            </div>

            <x-document-grid class="mt-6" :documents="$project->documents" empty="{{ __('No documents available for the project.') }}" />
            
            
        </div>
    </div>

</x-app-layout>
