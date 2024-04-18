<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event as BaseModel;
use Carbon\Carbon;

/**
 * Event model for mobile devices
 *
 * @OA\Schema()
 */
class EventDevice
{
    /**
     * Id of the event
     *
     * @var int
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

    /**
     * Date the event closes
     *
     * @var string
     * @OA\Property()
     */
    public ?string $closes = null;

    /**
     * Year of the event
     *
     * @var int
     * @OA\Property()
     */
    public ?int $year = null;

    /**
     * Website of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $website = null;

    /**
     * Name and address of the event venue
     *
     * @var string
     * @OA\Property()
     */
    public ?string $location = null;

    /**
     * Country where the venue is located
     *
     * @var string
     * @OA\Property()
     */
    public ?string $country = null;

    /**
     * Competitions linked to this event
     *
     * @var CompetitionDevice[]
     * @OA\Property()
     */
    public ?array $competitions;

    public function __construct(?BaseModel $event = null)
    {
        if (!empty($event)) {
            $this->country = $event->country->country_name;
            $this->id = $event->getKey();
            $this->name = $event->event_name;
            $this->opens = $event->event_open;
            $this->closes = (new Carbon($event->event_open))->addDays($event->event_duration)->toDateString();
            $this->year = intval($event->event_year);
            $this->website = $event->event_web;
            $this->location = $event->event_location;

            $this->competitions = [];
            foreach ($event->competitions as $c) {
                $this->competitions[] = new CompetitionDevice($c, false);
            }
        }
    }
}
