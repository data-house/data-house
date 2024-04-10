<?php

namespace App\Notifications;

use App\HelpAndSupport\Support;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class WelcomeUser extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected string $token)
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject(Lang::get('Your account for the :instance', ['instance' => config('app.name')]))
                    ->greeting(Lang::get('Dear :name, ', ['name' => $notifiable->name]))
                    ->line(Lang::get('as announced during the kick-off meeting, here are the login credentials to access the :instance.', ['instance' => config('app.name')]))
                    ->line('email: `'.$notifiable->email.'`')
                    ->line('password: `' . $this->token .'`')
                    ->line(Lang::get('We recommend changing the password after the first login by clicking on your username in the top right of the screen and selecting [Profile](:profile).', ['profile' => route('profile.show')]))
                    ->action('Login', route('login'))
                    ->line(Lang::get('Some useful links:'))
                    ->line(Lang::get('- [Guidelines and help](:link)', ['link' => Support::buildHelpPageLink()]))
                    ->line(Lang::get('- [Digital Library](:link)', ['link' => route('documents.library')]))
                    ->line(Lang::get('In case of problems please write to :email describing your problem.', ['email' => Support::supportEmail()]))
                    ->line(Lang::get('Looking forward to our cooperation!'));
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
}
