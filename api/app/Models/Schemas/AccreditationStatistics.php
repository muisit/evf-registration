<?php

namespace App\Models\Schemas;

/**
 * AccreditationStatistics information model
 *
 * @OA\Schema()
 */
class AccreditationStatistics
{
    /**
     * ID of the side event
     *
     * @var integer
     * @OA\Property()
     */
    public int $eventId = 0;

    /**
     * Total number of registrations
     *
     * @var string
     * @OA\Property()
     */
    public int $registrations = 0;

    /**
     * Total number of accreditations linked to this event
     *
     * @var string
     * @OA\Property()
     */
    public int $accreditations = 0;

    /**
     * Number of registrations still pending
     *
     * @var string
     * @OA\Property()
     */
    public int $pending = 0;

    /**
     * Total number of registrations present (badge handed out)
     *
     * @var string
     * @OA\Property()
     */
    public int $present = 0;

    /**
     * Total number of registrations cancelled
     *
     * @var string
     * @OA\Property()
     */
    public int $cancelled = 0;

    /**
     * Number of checkins of people enrolled for this event
     *
     * @var string
     * @OA\Property()
     */
    public int $checkin = 0;

    /**
     * Number of checkouts of people enrolled for this event
     *
     * @var string
     * @OA\Property()
     */
    public int $checkout = 0;
}
