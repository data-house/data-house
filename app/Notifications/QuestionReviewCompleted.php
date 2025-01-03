<?php

namespace App\Notifications;

use App\Models\QuestionReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class QuestionReviewCompleted extends Notification
{
    use Queueable;

    use RespectNotificationPreference;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public QuestionReview $review
    )
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resourceTitle = $this->review->question->questionable->title;

        return (new MailMessage)
                ->subject(Lang::get('Question/Answer review completed on :instance', ['instance' => config('app.name')]))
                ->greeting(Lang::get('Review of question completed :resource', ['resource' => $resourceTitle]))
                ->line(Lang::get('**:team** reviewed the question and answer asked to :resource:', ['team' => $this->review->team->name, 'resource' => $resourceTitle]))
                ->line($this->review->question->question)
                ->when($this->review->isAssigned($notifiable), function($notification): void{
                    $notification->action('See the review', route('question-reviews.show', $this->review));
                })
                ->when($this->review->isCoordinator($notifiable), function($notification): void{
                    $notification->action('See the review', route('question-reviews.show', $this->review));
                })
                ->when($this->review->isRequestor($notifiable), function($notification): void{
                    $notification->action('View the question', route('questions.show', $this->review->question));
                })
                ;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if($this->review->isRequestor($notifiable)){
            return [
                'question' => $this->review->question->uuid,
            ];
        }

        return [
            'review' => $this->review->getKey(),
        ];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'notification.question-review-completed';
    }
}
