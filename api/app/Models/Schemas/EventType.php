<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

/**
 * EventType model
 *
 * @OA\Schema()
 */
class EventType
{
    /**
     * Name of the event-type
     *
     * @var string
     * @OA\Property()
     */
    public string $name;

    public function __construct(?Event $event = null)
    {
        if (!empty($event)) {
            $this->name = $event->type?->name;
        }
    }
}
