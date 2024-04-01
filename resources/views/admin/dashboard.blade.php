<x-app-layout>
    <x-slot name="title">
        {{ __('Instance overview') }} - {{ __('Admin Area') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Instance overview')">

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
                            <a href="{{ route('documents.library') }}" class="font-medium text-blue-800 hover:text-blue-600">{{ __('Explore library') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pt-8 grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-8">
                <div class="flex flex-col overflow-hidden justify-between rounded-lg border border-stone-600/40 bg-white shadow" >
                    <div class="p-4 flex flex-col gap-2">
                        <div>
                            <h4 class="font-medium text-xl">{{ __('Statistics')}}</h4>
                        </div>
                        @forelse ($statistics as $label => $value)
                            <div class="flex justify-between">
                                <p>{{ $label }}</p>
                                <p class="text-right">{{ $value }}</p>
                            </div>
                        @empty
                            <p class="text-stone-600">{{ __('No usage statistics available') }}</p>
                        @endforelse
                    </div>
                    <div class="bg-stone-50 p-4 border-t border-stone-200">
                        <div class="text-sm flex divide-x-2 gap-2">
                            @if (config('pulse.enabled'))
                               <a target="_blank" href="{{ route('pulse') }}" class="font-medium text-blue-800 hover:text-blue-600">{{ __('View Instance Pulse') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex flex-col overflow-hidden justify-between rounded-lg border border-stone-600/40 bg-white shadow" >
                    <div class="p-4 flex flex-col">
                        <div>
                            <p class="font-medium text-xl mb-2">{{ __('Features')}}</p>
                        </div>
                        <p class="text-stone-600">{{ __('Feature panel is coming. See enabled features at a glance.') }}</p>
                    </div>
                </div>
                
            </div>
            
        </div>
    </div>
</x-app-layout>
