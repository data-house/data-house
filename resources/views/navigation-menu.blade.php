<nav x-data="{ open: false }" class="bg-white border-b border-stone-100">
    <!-- Primary Navigation Menu -->
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:gap-6 lg:ml-8 lg:gap-8">
                    <x-nav-link class="hidden md:inline-flex" href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
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

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Teams Dropdown -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->allTeams()->isNotEmpty())
                    <div class="hidden md:block ml-3 relative">
                        <x-dropdown align="right" width="60">
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
                                    <!-- Team Management -->
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


                                    <!-- Team Management -->
                                    <div class="block px-4 py-2 text-xs text-stone-400">
                                        {{ __('Manage Team') }}
                                    </div>

                                    <!-- Team Settings -->
                                    <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                        {{ __('Team Settings') }}
                                    </x-dropdown-link>

                                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                        <x-dropdown-link href="{{ route('teams.create') }}">
                                            {{ __('Create New Team') }}
                                        </x-dropdown-link>
                                    @endcan

                                    <div class="border-t border-stone-200"></div>

                                    <!-- Team Switcher -->
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
                @endif

                <!-- Settings Dropdown -->
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-stone-300 transition">
                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                </button>
                            @else
                                <span class="inline-flex rounded-md">
                                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-stone-500 bg-white hover:text-stone-700 focus:outline-none focus:bg-stone-50 active:bg-stone-50 transition ease-in-out duration-150">
                                        {{ Auth::user()->name }}

                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                        </svg>
                                    </button>
                                </span>
                            @endif
                        </x-slot>

                        <x-slot name="content">

                            <x-dropdown-link href="{{ route('stars.index') }}" :active="request()->routeIs('stars.*')">
                                <x-heroicon-o-star class="w-5 h-5 text-stone-600"  />

                                {{ __('Your stars') }}
                            </x-dropdown-link>

                            <div class="border-t border-stone-200/60 my-3"></div>

                            <x-dropdown-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.*')">
                                <x-heroicon-o-identification class="w-5 h-5 text-stone-600"  />

                                {{ __('Your profile') }}
                            </x-dropdown-link>

                            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                <x-dropdown-link href="{{ route('api-tokens.index') }}">
                                    {{ __('API Tokens') }}
                                </x-dropdown-link>
                            @endif

                            @can('admin-area')
                                <div class="border-t border-stone-200/60 my-3"></div>

                                <x-dropdown-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                                    <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-stone-600"  />

                                    {{ __('Admin area') }}
                                </x-dropdown-link>
                            @endcan


                            <div class="border-t border-stone-200/60 my-3"></div>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}" x-data>
                                @csrf

                                <x-dropdown-link href="{{ route('logout') }}"
                                         @click.prevent="$root.submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Notifications Dropdown -->
                <div class="ml-3 relative">
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
                
                <!-- Help and support Dropdown -->
                @support()
                <div class="ml-3 relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            
                            <button class="flex border-2 border-transparent rounded-full focus:outline-none focus:border-stone-300 transition"
                                title="{{ __('Help and feedback') }}">
                                <x-heroicon-o-question-mark-circle class="w-6 h-6 shrink-0" />
                            </button>
                            
                        </x-slot>

                        <x-slot name="content">

                            @if (\App\HelpAndSupport\Support::hasHelpPages())
                                <x-dropdown-link href="{{ \App\HelpAndSupport\Support::buildHelpPageLink() }}" target="_blank">
                                    {{ __('Help') }}
                                </x-dropdown-link>
                            @endif

                            @if (\App\HelpAndSupport\Support::hasTicketing())
                                <x-dropdown-link href="{{ \App\HelpAndSupport\Support::buildSupportTicketLink() }}" target="_blank">
                                    {{ __('Contact Support') }}
                                </x-dropdown-link>
                            @endif

                        </x-slot>
                    </x-dropdown>
                </div>
                @endsupport
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-stone-400 hover:text-stone-500 hover:bg-stone-100 focus:outline-none focus:bg-stone-100 focus:text-stone-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link href="{{ route('documents.library') }}" :active="request()->routeIs('documents.*') || request()->routeIs('imports.*') || request()->routeIs('mappings.*') || request()->routeIs('collections.*')">
                {{ __('Documents') }}
            </x-responsive-nav-link>
            @can('viewAny', \App\Models\Question::class)
                <x-responsive-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">{{ __('Questions') }}</x-responsive-nav-link>
            @endcan
            <x-responsive-nav-link href="{{ route('projects.index') }}" :active="request()->routeIs('projects.*')">
                {{ __('Projects') }}
            </x-responsive-nav-link>

            @support()
                @if (\App\HelpAndSupport\Support::hasHelpPages())
                    <x-responsive-nav-link href="{{ \App\HelpAndSupport\Support::buildHelpPageLink() }}" target="_blank">
                        {{ __('Help') }}
                    </x-responsive-nav-link>
                @endif

                @if (\App\HelpAndSupport\Support::hasTicketing())
                    <x-responsive-nav-link href="{{ \App\HelpAndSupport\Support::buildSupportTicketLink() }}" target="_blank">
                        {{ __('Contact Support') }}
                    </x-responsive-nav-link>
                @endif
            @endsupport
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-stone-200">
            <div class="flex items-center px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <div class="shrink-0 mr-3">
                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                    </div>
                @endif

                <div>
                    <div class="font-medium text-base text-stone-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-stone-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link href="{{ route('stars.index') }}" :active="request()->routeIs('stars.*')">
                    <x-heroicon-o-star class="w-5 h-5 text-stone-600"  />

                    {{ __('Your stars') }}
                </x-responsive-nav-link>

                <!-- Account Management -->
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    <x-heroicon-o-identification class="w-5 h-5 text-stone-600"  />

                    {{ __('Your profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                
                @can('admin-area')
                    <x-responsive-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-stone-600"  />

                        {{ __('Admin area') }}
                    </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}" x-data>
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                                   @click.prevent="$root.submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>

                <!-- Team Management -->
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->allTeams()->isNotEmpty())
                    <div class="border-t border-stone-200"></div>

                    <div class="block px-4 py-2 text-xs text-stone-400">
                        {{ __('Manage Team') }}
                    </div>

                    <!-- Team Settings -->
                    <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" :active="request()->routeIs('teams.show')">
                        {{ __('Team Settings') }}
                    </x-responsive-nav-link>

                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-responsive-nav-link href="{{ route('teams.create') }}" :active="request()->routeIs('teams.create')">
                            {{ __('Create New Team') }}
                        </x-responsive-nav-link>
                    @endcan

                    <div class="border-t border-stone-200"></div>

                    <!-- Team Switcher -->
                    <div class="block px-4 py-2 text-xs text-stone-400">
                        {{ __('Switch Teams') }}
                    </div>

                    @foreach (Auth::user()->allTeams() as $team)
                        <x-switchable-team :team="$team" component="responsive-nav-link" />
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</nav>
