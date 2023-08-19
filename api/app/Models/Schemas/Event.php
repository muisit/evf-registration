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
    public int $id;

    /**
     * Name (title) of the event
     *
     * @var string
     * @OA\Property()
     */
    public string $name;

    /**
     * Date the event starts
     *
     * @var string
     * @OA\Property()
     */
    public ?string $opens;

    /**
     * Date registration for this event is open
     *
     * @var string
     * @OA\Property()
     */
    public ?string $reg_open;

    /**
     * Date registration for this event closes
     *
     * @var string
     * @OA\Property()
     */
    public ?string $reg_close;

    /**
     * Year of the event
     *
     * @var int
     * @OA\Property()
     */
    public ?int $year;

    /**
     * Duration in days of the event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $duration;

    /**
     * E-mail address of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $email;

    /**
     * Website of the event organisation
     *
     * @var string
     * @OA\Property()
     */
    public ?string $web;

    /**
     * Name and address of the event venue
     *
     * @var string
     * @OA\Property()
     */
    public ?string $location;

    /**
     * Country where the venue is located
     *
     * @var string
     * @OA\Property()
     */
    public ?Country $country;

    /**
     * Type of the event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $type;

    /**
     * Bank and financial information on this event
     *
     * @var string
     * @OA\Property()
     */
    public ?Bank $bank;

    /**
     * Value indicating the way payments are to be processed
     *
     * @var string
     * @OA\Property()
     */
    public ?string $payments;

    /**
     * Live feed for the event
     *
     * @var string
     * @OA\Property()
     */
    public ?string $feed;

    /**
     * JSON configuration settings
     *
     * @var string
     * @OA\Property()
     */
    public ?string $config;

    public function __construct(?BaseModel $event = null)
    {
        if (!empty($event)) {
            $this->type = new EventType($event);
            $this->bank = new Bank($event);
            $this->country = new Country($event->country);

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
        }
    }
}
