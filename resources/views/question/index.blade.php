<x-app-layout>
    <x-slot name="title">
        {{ __('Question Library') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Question Library')">

            <x-slot:actions>
                
            </x-slot>

            @include('library-navigation-menu')
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="" method="get">
                    <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search the question library...') }}" />
                </form>
            </div>

            <div class="mt-6 grid grid-cols-3 gap-4">
                @forelse ($questions as $question)
                    <x-question-card :question="$question" />
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No questions in the library.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
