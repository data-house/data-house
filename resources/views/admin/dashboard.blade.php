<x-app-layout>
    <x-slot name="title">
        {{ __('Library overview') }} - {{ __('Admin Area') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Library overview')">

            <x-slot:actions>
                <x-heading-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">{{ __('Overview') }}</x-heading-nav-link>
                <x-heading-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">{{ __('Users') }}</x-heading-nav-link>
                <x-heading-nav-link href="{{ route('admin.projects.index') }}" :active="request()->routeIs('admin.projects.*')">{{ __('Projects') }}</x-heading-nav-link>
            </x-slot>
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-8">
                <div class="flex flex-col overflow-hidden justify-between rounded-lg border border-stone-600/40 bg-white shadow" >
                    <div class="p-4 flex justify-between">
                        <div>
                            <p class="text-stone-700">{{ __('Users')}}</p>
                            <p class="font-bold text-xl md:text-2xl lg:text-3xl">{{ $total_users }}</p>
                        </div>
                        <div>
                            <x-button-link href="#">{{ __('New user') }}</x-button-link>
                        </div>
                    </div>
                    <div class="bg-stone-50 p-4 border-t border-stone-200">
                        <div class="text-sm">
                            <a href="{{ route('admin.users.index') }}" class="font-medium text-blue-800 hover:text-blue-600">{{ __('Manage users') }}</a>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col overflow-hidden justify-between rounded-lg border border-stone-600/40 bg-white shadow" >
                    <div class="p-4 flex justify-between">
                        <div>
                            <p class="text-stone-700">{{ __('Projects')}}</p>
                            <p class="font-bold text-xl md:text-2xl lg:text-3xl">{{ $total_projects }}</p>
                        </div>
                        <div>
                            
                        </div>
                    </div>
                    <div class="bg-stone-50 p-4 border-t border-stone-200">
                        <div class="text-sm">
                            <a href="{{ route('admin.projects.index') }}" class="font-medium text-blue-800 hover:text-blue-600">{{ __('Manage projects') }}</a>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col overflow-hidden justify-between rounded-lg border border-stone-600/40 bg-white shadow" >
                    <div class="p-4 flex justify-between">
                        <div>
                            <p class="text-stone-700">{{ __('Documents')}}</p>
                            <p class="font-bold text-xl md:text-2xl lg:text-3xl">{{ $total_documents }}</p>
                        </div>
                        <div>
                            
                        </div>
                    </div>
                    <div class="bg-stone-50 p-4 border-t border-stone-200">
                        <div class="text-sm">
                            <a href="{{ route('documents.library') }}" class="font-medium text-blue-800 hover:text-blue-600">{{ __('Manage documents') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
