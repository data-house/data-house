<?php

namespace App\Notifications;

use App\HelpAndSupport\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject(Lang::get('Your account password has been changed', ['instance' => config('app.name')]))
                    ->greeting(Lang::get('Dear :name, ', ['name' => $notifiable->name]))
                    ->line(Lang::get('Your account password to access :instance has recently been changed.', ['instance' => config('app.name')]))
                    ->line(Lang::get('If you haven\'t performed the action, contact support at :email.', ['email' => Support::supportEmail()]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'notification.password-changed';
    }
}
