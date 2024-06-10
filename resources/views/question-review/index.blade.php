<x-app-layout>
    <x-slot name="title">
        {{ __('Question Reviews') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Question Reviews')">

            <x-slot:actions>
                
            </x-slot>

            @include('library-navigation-menu')
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                

            <div class="mt-6 flex flex-col gap-4">
                
                <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden px-4 text-sm text-stone-700">
                            
                    <div
                            @class([ 
                            'flex',
                            'gap-2',
                            'items-center',
                            'col-span-6',
                            ]) 
                        >
                            <div class="h-8 w-h-8" ></div>
                            
                            {{ __('Question') }}
                        </div>

                        <div
                            @class([ 
                            'col-span-2', 
                            ])>
                            {{ __('Assigned to') }}
                        </div>

                        <div
                            @class(['truncate', 
                                'col-span-2'
                                ])>
                            {{ __('Status') }}
                        </div>
                        
                        <div class="col-span-2">
                            {{ __('Requested on') }}
                        </div>
                        
                    </div>
                    @forelse ($reviews as $review)
                        <div class="grid grid-cols-12 gap-2 items-center rounded overflow-hidden bg-white px-4 py-3 group relative">
                            
                            <div
                                @class([ 
                                'flex',
                                'gap-2',
                                'items-center',
                                'col-span-6',
                                ]) 
                            >
                                
                                
                                <a href="{{ route('question-reviews.show', $review) }}" class=" block truncate group-hover:text-blue-800">
                                    <span class="z-10 absolute inset-0"></span>{{ $review->question->question }}
                                </a>

                            </div>

                            <div
                                @class([ 
                                'col-span-2 text-sm'
                                ])>
                                
                                {{ $review->assignees->map->name->join(', ') }}
                                
                            </div>

                            <div
                            @class(['truncate', 
                                'col-span-2'
                                ])>
                                <x-status-badge :status="$review->status" />
                            </div>

                            <div class="col-span-2">
                                {{ $review->created_at?->toDateString() }}
                            </div>
                        </div>
                    @empty
                        <div class="">
                            <p>{{ __('No question reviews assigned to your current team.') }}</p>
                        </div>
                    @endforelse


                <div class="mt-2">{{ $reviews?->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
