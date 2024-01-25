<?php

namespace App\Models\Schemas;

use App\Models\AccreditationTemplate as Model;

/**
 * Accreditation information model
 *
 * @OA\Schema()
 */
class AccreditationTemplate
{
    /**
     * ID of the template
     *
     * @var integer
     * @OA\Property()
     */
    public int $id = 0;

    /**
     * Name of the template
     *
     * @var string
     * @OA\Property()
     */
    public string $name = '';

    /**
     * Content
     *
     * @var object
     * @OA\Property()
     */
    public ?object $content = null;

    /**
     * Is this a default template
     *
     * @var string
     * @OA\Property()
     */
    public string $isDefault = 'N';

    /**
     * Related event
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $eventId = null;

    public function __construct(?Model $accreditationTemplate)
    {
        if (!empty($accreditationTemplate)) {
            $this->id = $accreditationTemplate->id;
            $this->name = $accreditationTemplate->name;
            $parsedContent = json_decode($accreditationTemplate->content);
            if ($parsedContent === false || !is_object($parsedContent)) {
                $this->content = null;
            }
            else {
                $this->content = $parsedContent;
            }
            $this->isDefault = $accreditationTemplate->is_default == 'Y' ? 'Y' : 'N';
            $this->eventId = $accreditationTemplate->event_id;
        }
    }
}
