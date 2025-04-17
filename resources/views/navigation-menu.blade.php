<nav x-data="{ open: false, user: false }" class="bg-white border-b border-stone-100">
    {{-- Primary Navigation Menu --}}
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">

                <div class="shrink-0 flex sm:hidden items-center -ml-3">
                    <x-flyer align="left">
                        <x-slot name="trigger">
                            <x-heroicon-o-bars-3-bottom-left class="w-5 h-5 text-stone-600"  />
                            <x-application-mark class="block h-8 w-auto" />
                        </x-slot>

                        <x-slot name="heading">

                            <div class="flex gap-2">
                                <x-application-mark class="block h-8 w-auto" />
                            </div>

                        </x-slot>

                        <x-slot name="content">
                            <x-flyer-link wire:navigate href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                                {{ __('Dashboard') }}
                            </x-flyer-link>
                            <x-flyer-link wire:navigate href="{{ route('documents.library') }}" :active="request()->routeIs('documents.*') || request()->routeIs('imports.*') || request()->routeIs('mappings.*') || request()->routeIs('collections.*')">
                                {{ __('Documents') }}
                            </x-flyer-link>
                            @can('viewAny', \App\Models\Question::class)
                                <x-flyer-link wire:navigate href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">{{ __('Questions') }}</x-flyer-link>
                            @endcan
                            <x-flyer-link wire:navigate href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                                {{ __('Projects') }}
                            </x-flyer-link>
                            <x-flyer-link wire:navigate href="{{ route('vocabularies.index') }}" :active="request()->routeIs('vocabularies.*') || request()->routeIs('vocabulary-concepts.*')">
                                {{ __('Vocabularies') }}
                            </x-flyer-link>
                        </x-slot>
                    </x-flyer>
                </div>

                {{-- Logo --}}
                <div class="hidden shrink-0 sm:flex items-center">
                    <x-nav-link href="{{ route('dashboard') }}" class="-ml-3" :active="request()->routeIs('dashboard')" style="padding-top:0;padding-bottom:0">
                        <x-application-mark class="block h-8 w-auto" />

                        <span class="hidden md:inline">{{ __('Dashboard') }}</span>
                    </x-nav-link>
                </div>

                {{-- Navigation Links --}}
                <div class="hidden sm:-my-px sm:ml-4 sm:flex sm:items-center sm:gap-4">
                    <x-nav-link href="{{ route('documents.library') }}" :active="request()->routeIs('documents.*') || request()->routeIs('imports.*') || request()->routeIs('mappings.*') || request()->routeIs('collections.*')">
                        {{ __('Documents') }}
                    </x-nav-link>
                    @can('viewAny', \App\Models\Question::class)
                        <x-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">{{ __('Questions') }}</x-nav-link>
                    @endcan
                    <x-nav-link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                        {{ __('Projects') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('vocabularies.index') }}" :active="request()->routeIs('vocabularies.*') || request()->routeIs('vocabulary-concepts.*')">
                        {{ __('Vocabularies') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="flex items-center sm:gap-1">
                

                

                {{-- Notifications Dropdown --}}
                <div class="">
                    <x-dropdown align="right" width="third" contentClasses="py-1 bg-white flex flex-col  min-h-[24rem] max-h-[24rem]">
                        <x-slot name="trigger">
                            <livewire:notifications.notification-bell />
                        </x-slot>

                        <x-slot name="content">

                            <div class="mb-4 flex items-center justify-between px-4 shrink-0">
                                <h4 class="font-medium text-stone-900">{{ __('Notifications') }}</h4>

                                <x-small-button type="button" @click="open = ! open">
                                    {{ __('Close')}}
                                </x-small-button>
                            </div>

                            <livewire:notifications.notifications-list x-on:notifications.window="$refresh" />
                            
                        </x-slot>
                    </x-dropdown>
                </div>
                

                {{-- Settings Dropdown --}}
                <div class="">
                    <x-flyer align="right">
                        <x-slot name="trigger">
                            <span class="font-medium hidden sm:inline" aria-hidden="true">{{ Auth::user()->name }}</span>
                            <span class="sr-only">{{ Auth::user()->name }}</span>
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="h-7 w-7 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" aria-hidden="true" />
                            @endif
                        </x-slot>

                        <x-slot name="heading">

                            <div class="flex gap-2">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <img class=" h-7 w-7 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" aria-hidden="true" />
                                @endif
                                <p>
                                    <span class="block font-medium">{{ Auth::user()->name }}</span>
                                    <span class="block text-xs">{{ Auth::user()->email }}</span>
                                </p>
                            </div>

                        </x-slot>

                        <x-slot name="content">

                            {{-- Teams Dropdown --}}
                            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->allTeams()->isNotEmpty())
                                <div class="relative">
                                    <x-dropdown align="left" width="60">
                                        <x-slot name="trigger">
                                            <span class="inline-flex rounded-md">
                                                <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-500 bg-white hover:text-stone-700 focus:outline-none focus:bg-stone-50 active:bg-stone-50 transition ease-in-out duration-150">
                                                    {{ Auth::user()->currentTeam->name }}

                                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </x-slot>

                                        <x-slot name="content">
                                            <div class="w-60">
                                                <div class="block px-4 py-2 text-xs text-stone-400">
                                                    {{ __('Projects') }}
                                                </div>

                                                @if (Auth::user()->currentTeam)
                                                    <button class="inline-flex items-center gap-2 w-full px-4 py-2 text-left text-sm leading-5 focus:outline-none transition duration-150 ease-in-out text-stone-700 hover:bg-stone-100 focus:bg-stone-100"
                                                        @click="Livewire.dispatch('openSlideover', {component: 'team-projects'}); open = !open"
                                                    >
                                                        {{ __('Team projects') }}
                                                    </button>
                                                    
                                                @endif

                                                <div class="border-t border-stone-200"></div>

                                                <div class="block px-4 py-2 text-xs text-stone-400">
                                                    {{ __('Manage Team') }}
                                                </div>

                                                <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                                    {{ __('Team Settings') }}
                                                </x-dropdown-link>

                                                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                                    <x-dropdown-link href="{{ route('teams.create') }}">
                                                        {{ __('Create New Team') }}
                                                    </x-dropdown-link>
                                                @endcan

                                                <div class="border-t border-stone-200"></div>

                                                <div class="block px-4 py-2 text-xs text-stone-400">
                                                    {{ __('Switch Teams') }}
                                                </div>

                                                @foreach (Auth::user()->allTeams() as $team)
                                                    <x-switchable-team :team="$team" />
                                                @endforeach
                                            </div>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                                <div class="border-t border-stone-200 mt-1.5 mb-2"></div>
                            @endif

                            <x-flyer-link wire:navigate href="{{ route('stars.index') }}" :active="request()->routeIs('stars.*')">
                                <x-heroicon-o-star class="w-5 h-5 text-stone-600"  />

                                {{ __('Your stars') }}
                            </x-flyer-link>

                            <x-flyer-link wire:navigate href="{{ route('profile.show') }}" :active="request()->routeIs('profile.*')">
                                <x-heroicon-o-identification class="w-5 h-5 text-stone-600"  />

                                {{ __('Your profile') }}
                            </x-flyer-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-flyer-link wire:navigate href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.*')">
                                    {{ __('API Tokens') }}
                                </x-flyer-link>
                            @endif

                            @can('admin-area')
                                <div class="border-t border-stone-200 mt-1.5 mb-2"></div>

                                <x-flyer-link wire:navigate href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                                    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-stone-600"  />

                                    {{ __('Admin area') }}
                                </x-flyer-link>
                            @endcan


                            
                            @support()
                                <div class="border-t border-stone-200 mt-1.5 mb-2"></div>

                                @if (\App\HelpAndSupport\Support::hasHelpPages())
                                    <x-flyer-link href="{{ \App\HelpAndSupport\Support::buildHelpPageLink() }}" target="_blank">
                                        <x-heroicon-o-book-open class="w-5 h-5 text-stone-600"  />
                                        {{ __('Documentation') }}
                                    </x-flyer-link>
                                @endif

                                @if (\App\HelpAndSupport\Support::hasTicketing())
                                    <x-flyer-link href="{{ \App\HelpAndSupport\Support::buildSupportTicketLink() }}" target="_blank">
                                        <x-heroicon-o-question-mark-circle class="w-5 h-5 text-stone-600"  />
                                        {{ __('Contact Support') }}
                                    </x-flyer-link>
                                @endif
                            @endsupport
                            
                            <div class="border-t border-stone-200 mt-1.5 mb-2"></div>
                            
                            {{-- Authentication --}}
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf
                                        
                                    <x-flyer-link href="{{ route('logout') }}"
                                        @click.prevent="$root.submit();">
                                        <x-heroicon-o-arrow-right-start-on-rectangle class="w-5 h-5 text-stone-600"  />
                                    {{ __('Log Out') }}
                                </x-flyer-link>
                            </form>

                            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                                <div class="mt-6">
                                    <p class="text-xs text-stone-600 mb-2">&copy; {{ __('OneOffTech and contributors.') }}</p>

                                    <p>
                                        <a target="_blank" href="{{ route('terms.show') }}" class="underline text-xs text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">{{ __('Terms of Service') }}</a>
                                        <a target="_blank" href="{{ route('policy.show') }}" class="underline text-xs text-stone-600 hover:text-stone-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-lime-500">{{ __('Privacy Policy') }}</a>
                                    </p>
                                </div>
                            @endif
                        </x-slot>
                    </x-flyer>
                </div>
            </div>
        </div>
    </div>
</nav>
