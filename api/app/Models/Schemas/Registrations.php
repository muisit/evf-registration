<?php

namespace App\Models\Schemas;

use App\Models\Registration as BaseModel;

/**
 * EventType model
 *
 * @OA\Schema()
 */
class Registrations
{
    /**
     * Contained registrations
     *
     * @var Registration[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Registration")
     * )
     */
    public ?array $registrations = null;

    /**
     * Contained fencers
     *
     * @var Fencer[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Fencer")
     * )
     */
    public ?array $fencers = null;

    public function add(BaseModel $registration)
    {
        if (empty($this->registrations)) {
            $this->registrations = [];
        }
        $this->registrations[] = new Registration($registration);

        $fencer = $registration->fencer;

        if (!empty($fencer)) {
            if (empty($this->fencers)) {
                $this->fencers = [];
            }
            $this->fencers[$fencer->getKey()] = new Fencer($fencer);
        }
    }

    public function finalize()
    {
        if (!empty($this->fencers)) {
            $this->fencers = array_values($this->fencers);
        }
    }
}
