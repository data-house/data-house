@if ($showMenu)

    <x-dropdown align="right">
        <x-slot name="trigger">
            <x-button type="button" class="justify-self-end inline-flex gap-1 items-center">
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
                        {{ __('View folder') }}
                    </x-dropdown-link>
                @endcan
            @endif
            @can('viewAny', \App\Model\Import::class)
                <x-dropdown-link 
                    href="{{ route('imports.index') }}"
                    :active="request()->routeIs('imports.*')"
                    >
                    {{ __('Imports') }}
                </x-dropdown-link>
            @endcan
        </x-slot>
    </x-dropdown>
@endif