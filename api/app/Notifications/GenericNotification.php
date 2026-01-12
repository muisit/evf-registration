<?php

namespace App\Notifications;

use App\Support\Services\GeneralNotificationService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;

class GenericNotification extends MailNotification
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('[EVF] General Notification')
            ->view('notifications.general', [
                "subject" => '[EVF] General Notification',
                "content" => $this->content
            ]);
    }
}
