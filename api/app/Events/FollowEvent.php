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

class FollowEvent extends BaseBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable;

    public Fencer $fencer;
    public DeviceUser $user;
    public bool $wasCancelled;

    public function __construct(Fencer $f, DeviceUser $u, bool $wasCancelled)
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
