<?php

namespace App\Models\Schemas;

use App\Models\EventType as Model;

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

    public function __construct(Model $data = null)
    {
        if (!empty($data)) {
            $this->name = $data->event_type_name;
        }
    }
}
