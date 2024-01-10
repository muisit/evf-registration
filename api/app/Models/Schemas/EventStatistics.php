<?php

namespace App\Models\Schemas;

/**
 * EventStatistics model
 *
 * @OA\Schema()
 */
class EventStatistics
{
    /**
     * ID of the event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Number of participants
     *
     * @var int
     * @OA\Property()
     */
    public int $participants = 0;

    /**
     * Number of registrations
     *
     * @var int
     * @OA\Property()
     */
    public int $registrations = 0;

    /**
     * Number of organisers
     *
     * @var int
     * @OA\Property()
     */
    public int $organisers = 0;

    /**
     * Number of support roles
     *
     * @var int
     * @OA\Property()
     */
    public int $support = 0;

    /**
     * Number of participants with a valid picture
     *
     * @var int
     * @OA\Property()
     */
    public int $hasPicture = 0;

    /**
     * Number of participants with a new (to be evaluated) picture
     *
     * @var int
     * @OA\Property()
     */
    public int $hasNewPicture = 0;

    /**
     * Number of participants with a picture that needs to be replaced
     *
     * @var int
     * @OA\Property()
     */
    public int $hasReplacePicture = 0;

    /**
     * Number of participants with no picture
     *
     * @var int
     * @OA\Property()
     */
    public int $hasNoPicture = 0;

    /**
     * Current queue entries
     *
     * @var int
     * @OA\Property()
     */
    public int $queue = 0;

    /**
     * Failed queue entries
     *
     * @var int
     * @OA\Property()
     */
    public int $failed = 0;
}
