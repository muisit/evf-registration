<?php

namespace App\Models\Schemas;

use App\Models\EventRole as Model;

/**
 * Event Role information model
 *
 * @OA\Schema()
 */
class EventRole
{
    /**
     * Id of the record
     *
     * @var int
     * @OA\Property()
     */
    public int $id;

    /**
     * User id
     *
     * @var int
     * @OA\Property()
     */
    public int $userId;

    /**
     * Name of the role
     *
     * @var string
     * @OA\Property()
     */
    public string $role;

    public function __construct(Model $event)
    {
        $this->id = $event->id;
        $this->userId = $event->user_id;
        $this->role = $event->role_type;
    }
}
