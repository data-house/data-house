<x-app-layout>
    <x-slot name="title">
        {{ __('Manage projects') }} - {{ __('Admin Area') }}
    </x-slot>
    <x-slot name="header">
        <x-page-heading :title="__('Manage projects')">

            <x-slot:actions>
                <x-heading-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">{{ __('Overview') }}</x-heading-nav-link>
                <x-heading-nav-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">{{ __('Users') }}</x-heading-nav-link>
                <x-heading-nav-link href="{{ route('admin.projects.index') }}" :active="request()->routeIs('admin.projects.*')">{{ __('Projects') }}</x-heading-nav-link>
            </x-slot>
        </x-page-heading>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <table class="w-full">
                <thead>
                    <tr>
                        <td class="p-2 w-8/12">Project title</td>
                        <td class="p-2 w-2/12">Added on</td>
                        <td class="p-2 w-2/12">Teams</td>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($projects as $project)
                        
                        <tr>
                            <td class="p-2">
                                <a target="_blank" class="underline" href="{{ route('projects.show', $project) }}">{{ $project->title }}</a>
                            </td>
                            <td class="p-2">{{ $project->created_at }}</td>
                            <td class="p-2">
                                {{ $project->teams->map(fn($team) => $team->name)->join(', ') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
        </div>
    </div>
</x-app-layout>
