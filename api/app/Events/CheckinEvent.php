<?php
 
namespace App\Events;
 
use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class CheckinEvent implements ShouldBroadcast
{
    public Event $event;
    public string $content;

    public function broadcastOn(): Channel
    {
        return new PrivateChannel([
            'checkin.' . $this->event->getKey(),
            'dt.' . $this->event->getKey(),
        ]);
    }
}
