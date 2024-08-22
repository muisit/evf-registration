<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Fencer;
use App\Models\Competition;

class RegisterForEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Fencer $fencer;
    public Competition $competition;
    public boolean $isCancelled;
    /**
     * Create a new event instance.
     */
    public function __construct(Fencer $fencer, Competition $competition, boolean $isCancelled)
    {
        $this->fencer = $fencer;
        $this->competition = $competition;
        $this->isCancelled = $cancelled;
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
