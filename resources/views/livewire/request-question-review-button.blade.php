@auth    

@php
    $iconStyle = 'group-hover/reviewers:text-stone-800';
    $buttonStyle = 'text-stone-600 hover:bg-stone-200 focus:bg-stone-200 active:bg-stone-300';
    
    if($this->isApproved || $this->isApprovedWithChanges){
        $iconStyle = 'group-hover/reviewers:text-green-700';
        $buttonStyle = 'bg-green-50 text-green-700 hover:bg-green-100 focus:bg-green-100 active:bg-green-100';
    }


@endphp

<button type="button"
    x-data
    x-on.review-requested.window="$refresh"
    
    @if ($this->isUnderReview)
    x-tooltip.raw="{{ __('An expert review is in progress. Click to see the status. You\'ll receive a notification once the review is completed.') }}"
    @elseif ($this->isApproved)
    x-tooltip.raw="{{ __('The answer was reviewed and approved by an expert.') }}"
    @elseif ($this->isApprovedWithChanges)
    x-tooltip.raw="{{ __('The expert made a few adjustments to the answer.') }}"
    @elseif ($this->isRejected)
    x-tooltip.raw="{{ __('The expert advises not to use the content of the answer.') }}"
    @else
    x-tooltip.raw="{{ __('Request an expert review of the answer.') . '&nbsp;' . __(':team members will be notified.', ['team' => $this->reviewerTeamNames->take(3)->join(', ')]) }}"
    @endif

    wire:click="$dispatch(
        'openSlideover', { 
            component: 'question-review.question-review-slideover', 
            arguments: { 
                question: '{{ $this->question->uuid }}'
            }
        })"
    class="group/reviewers text-sm inline-flex gap-1 items-center px-1 py-0.5 border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2 transition ease-in-out duration-150 {{ $buttonStyle }}">
    @if ($this->isUnderReview)
        <x-heroicon-o-ellipsis-horizontal-circle class="w-5 h-5 transition-all {{ $iconStyle }}"  />
        
        {{ __('Review in progress...') }}
    @elseif ($this->isApproved)
        <x-heroicon-o-check-circle class="w-5 h-5 transition-all {{ $iconStyle }}"  />
        
        {{ __('Approved') }}
    @elseif ($this->isApprovedWithChanges)
        <x-heroicon-o-check-circle class="w-5 h-5 transition-all {{ $iconStyle }}"  />
        
        {{ __('Reviewed') }}
    @elseif ($this->isRejected)
        <x-heroicon-o-x-circle class="w-5 h-5 transition-all {{ $iconStyle }}"  />
        
        {{ __('Rejected') }}
    @else
        <x-heroicon-o-users class="w-5 h-5 transition-all {{ $iconStyle }}"  />
        {{ __('Request a review') }}
    @endif
</button>
@endauth