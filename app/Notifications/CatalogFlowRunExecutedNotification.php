<?php

namespace App\Notifications;

use App\Models\CatalogFlowRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CatalogFlowRunExecutedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public CatalogFlowRun $flowRun
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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'run' => $this->flowRun->getKey(),
            'flow' => $this->flowRun->flow->title,
            'catalog_id' => $this->flowRun->flow->catalog->uuid,
            'catalog_name' => $this->flowRun->flow->catalog->title,
            'document_id' => $this->flowRun->document->ulid,
            'document_name' => $this->flowRun->document->title,
            'status' => $this->flowRun->status->label(),
        ];
    }

    /**
     * Get the notification's database type.
     *
     * @return string
     */
    public function databaseType(object $notifiable): string
    {
        return 'notification.catalog-flow-run-executed';
    }
}
