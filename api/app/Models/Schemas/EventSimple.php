<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event as BaseModel;

/**
 * Event model, simplified
 *
 * @OA\Schema()
 */
class EventSimple
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

    /**
     * JSON configuration settings
     *
     * @var string
     * @OA\Property()
     */
    public $config = null;

    public function __construct(?BaseModel $event = null)
    {
        if (!empty($event)) {
            $this->id = $event->getKey();
            $this->name = $event->event_name;
            $this->opens = $event->event_open;
            $this->config = json_decode($event->event_config);

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
