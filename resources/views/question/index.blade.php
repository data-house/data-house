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

    <div class="pt-8 pb-12">
        <div class="px-4 sm:px-6 lg:px-8">
            <div>
                <form action="" method="get">
                    <x-input type="text" :value="$searchQuery ?? null" name="s" id="s" class="min-w-full" placeholder="{{ __('Search the question library...') }}" />
                </form>
                @if ($searchQuery && $questions->isNotEmpty())
                    <div class="text-sm mt-3 py-2 text-right">{{ trans_choice(':total question found|:total questions found', $questions->total(), ['total' => $questions->total()]) }}</div>
                @endif

                @if (!$searchQuery && $questions->isNotEmpty())
                    <div class="text-sm mt-3 py-2 text-right">{{ trans_choice(':total question in the library|:total questions in the library', $questions->total(), ['total' => $questions->total()]) }}</div>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @forelse ($questions as $question)
                    <x-question-card :question="$question" />
                @empty
                    <div class="col-span-3">
                        <p>{{ __('No questions in the library.') }}</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-2">{{ $questions?->links() }}</div>
        </div>
    </div>
</x-app-layout>
