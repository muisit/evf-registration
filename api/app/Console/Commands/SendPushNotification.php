<?php

namespace App\Console\Commands;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Console\Command;
use App\Models\DeviceUser;

class SendPushNotification extends Command
{
    protected $signature = 'evf:push {user : the user id to send to}';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an FCM push notification';

    public function handle(): void
    {
        $uuid = $this->argument('user');
        $user = DeviceUser::where('uuid', $uuid)->first();
        if (empty($user)) {
            exit(1);
        }

        foreach ($user->devices as $device) {
            if (isset($device->platform['messagingToken'])) {
                $messaging = app('firebase.messaging');
                $message = CloudMessage::fromArray([
                    'token' => $device->platform['messagingToken'],
                    'notification' => [
                        'title' => 'Hello from EVF!',
                        'body' => 'This is a test notification.'
                    ]
                ]);
            }
        }
 
        $messaging->send($message);
    }
}
