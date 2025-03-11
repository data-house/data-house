@question()
@feature(Flag::questionWithAI())
    @can('viewAny', \App\Models\QuestionReview::class)
        <x-heading-nav-link href="{{ route('question-reviews.index') }}" :active="request()->routeIs('question-reviews.*')">{{ __('Question Reviews') }}</x-heading-nav-link>
    @endcan
@endfeature
@endquestion
