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
    public ?string $name = null;

    public function __construct(?Event $event = null)
    {
        if (!empty($event)) {
            $this->name = $event->type?->event_type_name;
        }
    }
}
