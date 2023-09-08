<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event as BaseModel;

/**
 * Event model
 *
 * @OA\Schema()
 */
class Event
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

    /**
     * Date registration for this event is open
     *
     * @var string
     * @OA\Property()
     */
    public ?string $reg_open = null;

    /**
     * Date registration for this event closes
     *
     * @var string
     * @OA\Property()
     */
    public ?string $reg_close = null;

    /**
     * Year of the event
     *
     * @var int
     * @OA\Property()
     */
    public ?int $year = null;

    /**
     * Duration in days of the event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $duration = null;

    /**
     * E-mail address of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $email = null;

    /**
     * Website of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $web = null;

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
    public ?int $countryId = null;

    /**
     * Type of the event
     *
     * @var string
     * @OA\Property()
     */
    public ?EventType $type = null;

    /**
     * Bank and financial information on this event
     *
     * @var string
     * @OA\Property()
     */
    public ?Bank $bank = null;

    /**
     * Value indicating the way payments are to be processed
     *
     * @var string
     * @OA\Property()
     */
    public ?string $payments = null;

    /**
     * Live feed for the event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $feed = null;

    /**
     * JSON configuration settings
     *
     * @var string
     * @OA\Property()
     */
    public ?string $config = null;

    /**
     * Related side events
     * 
     * @var SideEvent[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="SideEvent")
     * )
     */
    public ?array $sideEvents = null;

    /**
     * Related competitions
     * 
     * @var Competition[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Competition")
     * )
     */
    public ?array $competitions = null;

    public function __construct(?BaseModel $event = null)
    {
        if (!empty($event)) {
            $this->type = new EventType($event);
            $this->bank = new Bank($event);
            $this->countryId = $event->event_country;

            $this->id = $event->getKey();
            $this->name = $event->event_name;
            $this->opens = $event->event_open;
            $this->reg_open = $event->event_registration_open;
            $this->reg_close = $event->event_registration_close;
            $this->year = intval($event->event_year);
            $this->duration = intval($event->event_duration);
            $this->email = $event->event_email;
            $this->web = $event->event_web;
            $this->location = $event->event_location;
            $this->payments = $event->event_payments;
            $this->feed = $event->event_feed;
            $this->config = $event->event_config;

            // $event->event_in_ranking
            // $event->event_factor
            // $event->event_frontend

            $this->sideEvents = [];
            foreach ($event->sides()->orderBy('title')->get() as $sideEvent) {
                $this->sideEvents[] = new SideEvent($sideEvent);
            }

            $this->competitions = [];
            foreach ($event->competitions as $competition) {
                $this->competitions[] = new Competition($competition);
            }
        }
    }
}
