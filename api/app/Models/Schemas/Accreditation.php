<?php

namespace App\Models\Schemas;

use App\Models\Accreditation as Model;

/**
 * Accreditation information model
 *
 * @OA\Schema()
 */
class Accreditation
{
    /**
     * ID of the accreditation
     *
     * @var integer
     * @OA\Property()
     */
    public int $id;

    /**
     * Fencer ID
     *
     * @var string
     * @OA\Property()
     */
    public int $fencerId;

    /**
     * Event ID
     *
     * @var string
     * @OA\Property()
     */
    public int $eventId;

    /**
     * Template name
     */
    public ?string $template;

    /**
     * Has File indicator
     */
    public string $hasFile;
    
    public function __construct(?Model $accreditation)
    {
        if (!empty($accreditation)) {
            $this->id = $accreditation->id;
            $this->fencerId = $accreditation->fencer_id;
            $this->eventId = $accreditation->event_id;
            $this->template = $accreditation->template?->name;
            $this->hasFile = !empty($accreditation->generated) && !empty($accreditation->file_hash) ? 'Y' : 'N';
        }
    }
}
