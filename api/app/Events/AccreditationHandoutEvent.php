<?php
 
namespace App\Events;
 
use App\Models\Accreditation;
use App\Models\Registration;
use App\Models\Fencer;
use App\Models\Event;
use App\Models\Schemas\Fencer as FencerSchema;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Carbon\Carbon;

class AccreditationHandoutEvent extends BaseBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;

    private Accreditation $accreditation;
    private string $datetime;

    public function __construct(Accreditation $accreditation)
    {
        $this->accreditation = $accreditation;
        $this->event = $accreditation->event;
        $this->fencer = $accreditation->fencer;
        $this->datetime = Carbon::now()->toDateTimeString();
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('dt.' . $this->event->getKey()),
        ];
    }

    public function broadcastWith()
    {
        return (array) (new FencerSchema($this->accreditation->fencer, $this->event));
    }
}
