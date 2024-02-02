<x-heading-nav-link href="{{ route('documents.library') }}" :active="request()->routeIs('documents.*')">{{ __('Documents') }}</x-heading-nav-link>

@question()
    @can('viewAny', \App\Models\Question::class)
        <x-heading-nav-link href="{{ route('questions.index') }}" :active="request()->routeIs('questions.*')">{{ __('Questions') }}</x-heading-nav-link>
    @endcan
@endquestion
