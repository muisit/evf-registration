<?php

namespace App\Console\Commands;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Console\Command;

class SendPushNotification extends Command
{
    protected $signature = 'evf:dirty {user: the user id to send to';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an FCM push notification';

    public function handle(): void
    {
        $firebase = (new Factory())
            ->withServiceAccount(config_path('/firebase_credentials.json'));
 
        $messaging = $firebase->createMessaging();
 
        $message = CloudMessage::fromArray([
            'notification' => [
                'title' => 'Hello from Firebase!',
                'body' => 'This is a test notification.'
            ],
            'topic' => 'global'
        ]);
 
        $messaging->send($message);
    }
}
