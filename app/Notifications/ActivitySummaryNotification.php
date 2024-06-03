<?php

namespace App\Notifications;

use App\Data\Notifications\ActivitySummaryNotificationData;
use App\Data\NotificationSettingsData;
use Carbon\CarbonPeriodImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class ActivitySummaryNotification extends Notification
{
    use Queueable;

    use RespectNotificationPreference;

    

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CarbonPeriodImmutable $period,
        public int $total_documents_added = 0,
        public ?Collection $documents = null,
        public ?Collection $projects = null,
        )
    {
        //
    }

    protected function shouldSendUsing($notifiable, NotificationSettingsData $preferences, $channel): bool
    {
        if($channel !== 'mail'){
            return false;
        }

        return $preferences->activitySummary?->enable ?? (new ActivitySummaryNotificationData())->enable;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        return (new MailMessage)
            ->subject(Lang::get('Activity at :instance', ['instance' => config('app.name')]))
            ->greeting(Lang::get('Hello :name, ', ['name' => $notifiable->name]))
            ->line(Lang::get('There was some activity at :instance between :start and :end.', [
                'instance' => config('app.name'), 
                'start' => $this->period->getStartDate()->toDateString(),
                'end' => $this->period->getEndDate()->toDateString(),
                ]))
            ->when($this->total_documents_added > 0, function($message){
                $message->line(Lang::choice('{0} **Documents added**|{1} **:num Document added**|[2,*] **:num Documents added**', $this->total_documents_added, [
                    'num' => $this->total_documents_added,
                ]));
                
                $docs = $this->documents->map(fn($doc) => $doc->project ? "- [{$doc->title}]({$doc->pageUrl()}) ([{$doc->project->title}]({$doc->project->url()}))" : "- [{$doc->title}]({$doc->pageUrl()})");

                $message->lines([
                    Lang::get('Here are some recent additions:'),
                    ...$docs->toArray(),
                ]);
            })
            ->action(Lang::get('Explore the library'), route('documents.library'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [];
    }
}
