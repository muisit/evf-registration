<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event as BaseModel;

/**
 * Event model, simplified
 *
 * @OA\Schema()
 */
class EventSimple
{
    /**
     * ID of the event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Name (title) of the event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $name = null;

    /**
     * Date the event starts
     *
     * @var string
     * @OA\Property()
     */
    public ?string $opens = null;

    public function __construct(?BaseModel $event = null)
    {
        if (!empty($event)) {
            $this->id = $event->getKey();
            $this->name = $event->event_name;
            $this->opens = $event->event_open;
        }
    }
}
