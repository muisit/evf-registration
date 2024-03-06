<?php
 
namespace App\Events;
 
use App\Models\AccreditationDocument;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Schemas\AccreditationDocument as Schema;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Carbon\Carbon;

class ProcessEndEvent extends BaseBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;

    private Event $event;
    private AccreditationDocument $content;

    public function __construct(Event $e, AccreditationDocument $c)
    {
        \Log::debug("creating ProcessEndEvent");
        $this->event = $e;
        $this->content = $c;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('checkin.' . $this->event->getKey()),
            new PrivateChannel('checkout.' . $this->event->getKey()),
        ];
    }

    public function broadcastWith()
    {
        return (array) (new Schema($this->content));
    }
}
