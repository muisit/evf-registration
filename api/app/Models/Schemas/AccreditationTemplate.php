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
    public int $id;

    /**
     * Name of the template
     *
     * @var string
     * @OA\Property()
     */
    public string $name;

    /**
     * Content
     *
     * @var object
     * @OA\Property()
     */
    public object $content;
  
    public function __construct(?Model $accreditationTemplate)
    {
        if (!empty($accreditationTemplate)) {
            $this->id = $accreditationTemplate->id;
            $this->name = $accreditationTemplate->name;
            $this->content = json_decode($accreditationTemplate->content);
            if ($this->content === false) {
                $this->content = (object)[];
            }
        }
    }
}
