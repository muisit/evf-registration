<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\RankingPosition;

class RankingUpdate
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public RankingPosition $ranking;

    /**
     * Create a new event instance.
     */
    public function __construct(RankingPosition $ranking)
    {
        $this->fencer = fencer;
        $this->ranking = $ranking;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}
