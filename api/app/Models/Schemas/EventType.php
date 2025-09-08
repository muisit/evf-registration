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
     * Id of the event-type
     *
     * @var integer
     * @OA\Property()
     */
    public int $id = -1;

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
            $this->id = $data->event_type_id;
            $this->name = $data->event_type_name;
        }
    }
}
