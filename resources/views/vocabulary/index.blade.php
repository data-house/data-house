<x-app-layout>
    <x-slot name="title">
        {{ __('Vocabularies') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Vocabularies')">

            <x-slot:actions>

            </x-slot>

        </x-page-heading>
    </x-slot>

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">                
               
            @forelse ($vocabularies as $vocabulary)
                
                <div class="space-y-2 rounded overflow-hidden bg-white p-4 group relative">
                    


                    <a href="{{ route('vocabularies.show', $vocabulary) }}" class="block font-bold group-hover:text-blue-800">
                        <span class="z-10 absolute inset-0"></span>{{ $vocabulary->pref_label }}
                    </a>

                    <div class="flex flex-wrap gap-2">
                    </div>

                </div>

            @empty

            <div class=" md:col-span-2 lg:col-span-3">
                <p>{{ __('No vocabularies yet. Ask your administrator to import them.') }}</p>
            </div>
                
            @endforelse


        </div>
    </div>
</x-app-layout>
