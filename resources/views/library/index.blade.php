<x-app-layout>
    <x-slot name="title">
        {{ __('Digital Library') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Digital Library') }}
            </h2>
            <div class="flex gap-2">
                @can('create', \App\Model\Document::class)
                    <x-button-link href="{{ route('documents.create') }}">
                        {{ __('Upload Document') }}
                    </x-button-link>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="" method="get">
                    <x-input type="text" name="s" id="s" class="min-w-full" placeholder="{{ _('Search within the digital library...') }}" />
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
