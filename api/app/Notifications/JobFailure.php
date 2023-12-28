<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;

class JobFailure extends MailNotification
{
    private $content;

    public function __construct(JobFailed $event)
    {
        // $event->connectionName
        // $event->job
        // $event->exception
        $this->content = "Job " . $event->job->uuid() . ' (' . json_encode($event->job->payload()) . ') failed to execute.';
    }

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
            ->subject('[EVF] Job Failed')
            ->view('notifications.jobfailed', [
                "subject" => '[EVF] Job Failed',
                "content" => $this->content,
                "baselink" => "<a href='" . $baselink . "'>Application</a>",
            ]);
    }

}
