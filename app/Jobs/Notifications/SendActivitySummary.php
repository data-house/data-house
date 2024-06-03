<?php

namespace App\Jobs\Notifications;

use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Models\Document;
use App\Models\User;
use App\Notifications\ActivitySummaryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendActivitySummary implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user
    )
    {
        //
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'sas_' . $this->user->getKey();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /**
         * @var \App\Data\Notifications\ActivitySummaryNotificationData
         */
        $preference = $this->user->notification_settings?->activitySummary ?? new ActivitySummaryNotificationData();

        if(!$preference->enable){
            return;
        }

        $period = $preference->frequency->getPeriod();

        // In the notification we will show
        // - The number of documents added to the library that the user can see
        // - A preview of the latest 10 documents added
        // - The name of the projects that got new documents

        $totalAddedDocs = Document::query()
            ->visibleBy($this->user)
            ->where('created_at', '>=', $period->getStartDate())
            ->where('created_at', '<=', $period->getEndDate())
            ->count();
        
        $lastAddedDocs = Document::query()
            ->visibleBy($this->user)
            ->where('created_at', '>=', $period->getStartDate())
            ->where('created_at', '<=', $period->getEndDate())
            ->latest()
            ->limit(5)
            ->with('project')
            ->get();

        $projects = $lastAddedDocs->map->project->unique()->filter();

        if($totalAddedDocs == 0){
            // No need to send a completly empty notification
            return;
        }

        $this->user->notifyNow(new ActivitySummaryNotification(
            period: $period,
            total_documents_added: $totalAddedDocs,
            documents: $lastAddedDocs,
            projects: $projects,
        ));
    }
}
