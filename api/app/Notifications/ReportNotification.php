<?php

namespace App\Notifications;

use App\Support\Services\GeneralNotificationService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;

class ReportNotification extends MailNotification
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $feurl = env('APP_FE_URL', env('APP_URL'));
        $baselink = htmlentities(url($feurl), ENT_QUOTES, 'utf-8');

        return (new MailMessage())
            ->subject('[EVF] General Notification')
            ->view('notifications.general', [
                "subject" => '[EVF] General Notification',
                "content" => (new GeneralNotificationService())->generate(),
                "baselink" => "<a href='" . $baselink . "'>Application</a>",
            ]);
    }
}
