<?php

namespace App\Models\Schemas;

use App\Models\AccreditationUser as Model;

/**
 * AccreditationUser information model
 *
 * @OA\Schema()
 */
class AccreditationUser
{
    /**
     * ID of the record
     *
     * @var integer
     * @OA\Property()
     */
    public int $id = 0;

    /**
     * Related event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $eventId = null;

    /**
     * Related fencer record
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $fencerId = null;

    /**
     * User authorization type
     *
     * @var string
     * @OA\Property()
     */
    public ?string $type = null;

    /**
     * Related Accreditation badge id
     *
     * @var string
     * @OA\Property()
     */
    public ?string $badge = null;

    public function __construct(?Model $user)
    {
        if (!empty($user)) {
            $this->id = $user->getKey();
            $this->eventId = $user->event_id;
            $this->fencerId = $user->accreditation->fencer_id;
            $this->type = $user->type;
            $this->badge = $user->accreditation->getFullAccreditationId();
        }
    }
}
