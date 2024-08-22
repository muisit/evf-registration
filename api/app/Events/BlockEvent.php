<?php
 
namespace App\Events;
 
use App\Models\Fencer;
use App\Models\DeviceUser;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Carbon\Carbon;

class BlockEvent extends BaseBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;

    private Fencer $fencer;
    private DeviceUser $user;
    private boolean $wasCancelled;

    public function __construct(Fencer $f, DeviceUser $u, boolean $wasCancelled)
    {
        $this->fencer = $f;
        $this->user = $u;
        $this->wasCancelled = $wasCancelled;
    }

    public function broadcastOn()
    {
        return [];
    }
}
