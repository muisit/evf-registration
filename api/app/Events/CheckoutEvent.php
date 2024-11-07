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

class CheckoutEvent extends BaseBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;

    public Event $event;
    public AccreditationDocument $content;

    public function __construct(Event $e, AccreditationDocument $c)
    {
        \Log::debug("creating CheckoutEvent");
        $this->event = $e;
        $this->content = $c;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('checkin.' . $this->event->getKey()),
            new PrivateChannel('checkout.' . $this->event->getKey()),
            new PrivateChannel('dt.' . $this->event->getKey()),
        ];
    }

    public function broadcastWith()
    {
        return (array) (new Schema($this->content));
    }
}
