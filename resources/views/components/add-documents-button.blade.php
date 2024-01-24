@if (Auth::user()->can('create', \App\Model\Document::class) ||
     Auth::user()->can('viewAny', \App\Model\Import::class) ||
     config('library.upload.allow_direct_upload'))

    <x-dropdown align="right">
        <x-slot name="trigger">
            <x-button type="button" class="justify-self-end inline-flex gap-1 items-center">
                {{ __('Add documents') }}
            </x-button>
        </x-slot>
    
        <x-slot name="content">

            @can('create', \App\Model\Document::class)
                @if (config('library.upload.allow_direct_upload'))
                    <x-dropdown-link 
                        href="{{ route('documents.create') }}"
                        :active="request()->routeIs('documents.create')"
                        >
                        {{ __('Upload Document') }}
                    </x-dropdown-link>
                @endif
            @endcan
            @can('viewAny', \App\Model\Import::class)
                <x-dropdown-link 
                    href="{{ route('imports.index') }}"
                    :active="request()->routeIs('imports.*')"
                    >
                    {{ __('Import Documents') }}
                </x-dropdown-link>
            @endcan
        </x-slot>
    </x-dropdown>
@endif