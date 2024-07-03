@if ($showMenu)

    <x-dropdown align="right">
        <x-slot name="trigger">
            <x-button type="button" class="justify-self-end inline-flex gap-1 items-center whitespace-nowrap">
                {{ __('Manage documents') }}
            </x-button>
        </x-slot>
    
        <x-slot name="content">

            @if ($directUploadEnabled)
                @can('create', \App\Model\Document::class)
                    <x-dropdown-link 
                        href="{{ route('documents.create') }}"
                        :active="request()->routeIs('documents.create')"
                        >
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4 text-stone-600"  />
                        {{ __('Upload Document') }}
                    </x-dropdown-link>
                @endcan
            @endif
            @if (!$directUploadEnabled && $uploadLink)
                @can('create', \App\Model\Document::class)
                    <x-dropdown-link 
                        href="{{ $uploadLink }}"
                        target="_blank"
                        >
                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 text-stone-600"  />
                        {{ __('View folder') }}
                    </x-dropdown-link>
                @endcan
            @endif
            @can('viewAny', \App\Model\Import::class)
                <x-dropdown-link 
                    href="{{ route('imports.index') }}"
                    :active="request()->routeIs('imports.*')"
                    >
                        <x-heroicon-o-arrows-right-left class="w-4 h-4 text-stone-600"  />
                    {{ __('Imports') }}
                </x-dropdown-link>
            @endcan
        </x-slot>
    </x-dropdown>
@endif