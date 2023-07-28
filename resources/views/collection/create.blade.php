<x-app-layout>
    <x-slot name="title">
        {{ __('Create collection') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Create a collection') }}
            </h2>
            <div class="flex gap-2">
                
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="{{ route('collections.store') }}" method="post" enctype="multipart/form-data">

                    @csrf

                    <div>
                        <x-label for="title" value="{{ __('Collection title') }}" />
                        <x-input-error for="title" class="mt-2" />
                        <x-input id="title" type="text" name="title" class="mt-1 block w-full max-w-prose" autocomplete="title" value="{{ old('title') }}" />
                    </div>

                    
                    <div class="flex items-center gap-4">
                        <x-button class="">
                            {{ __('Create') }}
                        </x-button>

                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
