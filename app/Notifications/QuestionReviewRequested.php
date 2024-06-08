<?php

namespace App\Notifications;

use App\Models\QuestionReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class QuestionReviewRequested extends Notification
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
                ->subject(Lang::get('You\'ve been invited to review a question/answer on :instance', ['instance' => config('app.name')]))
                ->greeting(Lang::get('A new answer review is waiting you on :resource', ['resource' => $resourceTitle]))
                ->line(Lang::get('You have been assigned as reviewer of the following question asked to :resource.', ['resource' => $resourceTitle]))
                ->line($this->review->question->question)
                ->action('Review the question', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
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
        return 'notification.question-review-assigned';
    }
}
