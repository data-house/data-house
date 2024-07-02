<x-app-layout>
    <x-slot name="title">
        {{ __('New import') }}
    </x-slot>
    <x-slot name="header">
        <div class="md:flex md:items-center md:justify-between relative">
            <h2 class="font-semibold text-xl text-stone-800 leading-tight">
                {{ __('Create an import from external providers') }}
            </h2>
            <div class="flex gap-2">
                
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">                
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <x-section-title>
                    <x-slot name="title">{{ __('Import from external source') }}</x-slot>
                    <x-slot name="description">{{ __('Data House can connect to various document management services to fetch files to import.') }}</x-slot>
                </x-section-title>
            
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <form action="{{ route('imports.store') }}" method="post">
                        
                        @csrf


                        <div class="px-4 py-5 bg-white sm:p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                            <div class="grid grid-cols-6 gap-6">

                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="source" value="{{ __('Source') }}" />

                                    <select name="source" id="source" class="mt-1 block w-full border-stone-300 focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm">
                                        @foreach ($sources as $source)
                                            <option value="{{ $source->value }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="source" class="mt-2" />
                                </div>
                                
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="url" value="{{ __('URL') }}" />
                                    <x-input name="url" id="url" type="text" class="mt-1 block w-full" autocomplete="url" :value="old('url', '')" />
                                    <x-input-error for="url" class="mt-2" />
                                </div>
                        
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="user" value="{{ __('Username') }}" />
                                    <x-input name="user" id="user" type="text" class="mt-1 block w-full" autocomplete="username" :value="old('user', '')" />
                                    <x-input-error for="user" class="mt-2" />
                                </div>
                        
                                <div class="col-span-6 sm:col-span-4">
                                    <x-label for="password" value="{{ __('Password') }}" />
                                    <x-input name="password" id="password" type="password" class="mt-1 block w-full" autocomplete="password" />
                                    <x-input-error for="password" class="mt-2" />
                                </div>

                            </div>
                        </div>
            
                        
                        <div class="flex items-center justify-end px-4 py-3 bg-stone-50 text-right sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                            <x-button type="submit">
                                {{ __('Create Import') }}
                            </x-button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
