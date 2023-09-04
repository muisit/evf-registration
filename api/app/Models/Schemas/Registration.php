<?php

namespace App\Models\Schemas;

use App\Models\Registration as BaseModel;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class Registration
{
    /**
     * Unique registration id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Fencer identifier
     *
     * @var int
     * @OA\Property()
     */
    public ?int $fencerId = null;

    /**
     * Role identifier
     *
     * @var int
     * @OA\Property()
     */
    public ?int $roleId = null;

    /**
     * Side event identifier
     *
     * @var int
     * @OA\Property()
     */
    public ?int $sideEventId = null;

    /**
     * DateTime of the registration
     *
     * @var string
     * @OA\Property()
     */
    public ?string $dateTime = null;

    /**
     * Payment method
     *
     * @var string
     * @OA\Property()
     */
    public ?string $payment = null;

    /**
     * Status of payment to organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $paid = null;

    /**
     * Status of payment to HoD
     *
     * @var string
     * @OA\Property()
     */
    public ?string $paidHod = null;

    /**
     * Status of the registration
     *
     * @var string
     * @OA\Property()
     */
    public ?string $state = null;

    /**
     * Team name this registration is a part of
     *
     * @var string
     * @OA\Property()
     */
    public ?string $team = null;

    public function __construct(BaseModel $registration)
    {
        $this->id = $registration->getKey();
        $this->fencerId = $registration->registration_fencer;
        $this->sideEventId = $registration->registration_event;
        $this->roleId = intval($registration->registration_role) > 0 ? $registration->registration_role : null;
        $this->dateTime = $registration->registration_date;
        $this->payment = $registration->registration_payment;
        $this->paid = $registration->registration_paid;
        $this->paidHod = $registration->registration_paid_hod;
        $this->state = $registration->registration_state;
        $this->team = $registration->registration_team;
    }
}
