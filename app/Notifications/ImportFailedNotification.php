<?php

namespace App\Notifications;

use App\Models\ImportMap;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ImportFailedNotification extends Notification
{
    use Queueable;

    use RespectNotificationPreference;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ImportMap $importMap
        )
    {
        //
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $import = $this->importMap->import;

        return (new MailMessage)
            ->subject(Lang::get(':instance Import failed: :map', ['instance' => config('app.name'), 'map' => $this->importMap->label()]))
            ->greeting(Lang::get(':import mapping run', ['import' => $import->label()]))
            ->line(Lang::get('We failed to process the mapping **:map** configured in **:import**.', [
                'map' => $this->importMap->label(), 
                'import' => $import->label()
                ]))
            ->action(Lang::get('View mapping'), route('mappings.show', $this->importMap));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'map' => $this->importMap->ulid,
        ];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'notification.import-failed';
    }
}
