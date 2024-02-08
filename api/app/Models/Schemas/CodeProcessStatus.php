<?php

namespace App\Models\Schemas;

use App\Models\Fencer as FencerModel;
use App\Models\Event;

/**
 * Basic return value
 *
 * @OA\Schema()
 */
class CodeProcessStatus
{
    /**
     * ID of the event
     *
     * @var integer
     * @OA\Property()
     */
    public int $eventId = 0;

    /**
     * Status value
     *
     * @var string
     * @OA\Property(
     *     enum = {"ok", "error"}
     * )
     *
     */
    public string $status;

    /**
     * Action value
     *
     * @var string
     * @OA\Property()
     *
     */
    public string $action;

    /**
     * In case of errors, this can possibly contain an informative error message.
     *
     * @var string
     * @OA\Property()
     */
    public ?string $message;

    /**
     * Optional data field: fencer
     */
    public ?Fencer $fencer = null;

    public function __construct($id = 0, $status = 'error', $action = 'error', ?string $message = null)
    {
        $this->eventId = $id;
        $this->status = $status;
        $this->action = $action;
        $this->message = $message;
    }

    public function setFencer(FencerModel $fencer, ?Event $event)
    {
        $this->fencer = new Fencer($fencer, $event);
    }
}
