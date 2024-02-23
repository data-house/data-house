<x-app-layout>
    <x-slot name="title">
        {{ __('Upload document') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Upload a document to the digital library') }}
            </h2>
            <div class="flex gap-2">
                
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">                
            <div>
                <form action="{{ route('documents.store') }}" method="post" enctype="multipart/form-data">

                    @csrf

                    <div class="mb-4">
                        <x-input-error for="file" />
                        <input type="file" name="document" id="document">
                    </div>

                    
                    <x-button>
                        {{ __('Upload Document') }}
                    </x-button>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
