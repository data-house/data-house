<div>
    @if ($projectSelection)
        <div class="border border-stone-300 rounded p-2 bg-white">
            <p class="flex justify-between mb-2">
                <x-label>{{ __('Link a project') }}</x-label>

                <x-small-button wire:click="$toggle('projectSelection')">
                    {{ __('Close')}}
                </x-small-button>
            </p>

            @if ($project)
                <div class="mb-2 space-y-1">
                    <x-label>{{ __('Current project') }}</x-label>
                    
                    <div class="flex justify-between">
                        <p class="text-sm">{{ $project->title }}</p>

                        <x-small-button wire:click="unlinkProject">
                            {{ __('Remove')}}
                        </x-small-button>
                    </div>
                </div>
            @endif


            <p class="flex justify-between mb-2">
                <x-label>{{ __('Select a project') }}</x-label>
            </p>
            
            <div class="relative text-sm mb-2">
                <x-input type="text" name="doc_prj_s" wire:model.live.debounce.500ms="search" id="doc_prj_s" class="min-w-full" placeholder="{{ __('Search projects...') }}" />
                
                <div wire:loading wire:target="search" class="absolute top-0 right-0 flex items-center h-full p-2 text-orange-50 bg-orange-600 rounded-r-md ">
                    {{ __('Searching...') }}
                </div>
            </div>

            <ul class="h-96 overflow-y-auto -mx-2 flex flex-col">
                @forelse ($selectableProjects as $prj)
                    <li wire:key="{{ $prj->getKey() }}" class=""><button  wire:click="linkProject('{{ $prj->getKey() }}')" class="inline-flex items-start w-full gap-2 p-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100">
                        @if ($prj->getKey() === $project?->getKey())
                            <x-heroicon-m-check-circle class="size-4 shrink-0" />
                        @else
                            <span class="block size-4 shrink-0"></span>
                        @endif
                        {{ $prj->title }}
                    </button></li>
                @empty
                    <li class="text-sm text-stone-600 p-2">{{ __('No projects') }}</li>
                @endforelse
            </ul>
        </div>
    @endif

    @unless ($projectSelection)
        <p>
            <x-small-button wire:click="$toggle('projectSelection')">
                @if ($project)
                    {{ __('Edit')}}
                @else
                    {{ __('Link a project')}}
                @endif
            </x-small-button>
        </p>

        @if ($project)
            <x-project-card :project="$project" />
        @else
            <p class="prose">{{ __('Project not identified') }}</p>
        @endif
    @endunless

</div>