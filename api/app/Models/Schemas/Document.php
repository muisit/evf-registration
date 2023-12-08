<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Event;

/**
 * Document information model
 *
 * @OA\Schema()
 */
class Document
{
    /**
     * Document id
     *
     * @var int
     * @OA\Property()
     */
    public int $id = 0;

    /**
     * Human readable size
     *
     * @var string
     * @OA\Property()
     */
    public string $size = '';

    /**
     * Availability
     *
     * @var string
     * @OA\Property()
     */
    public string $available = 'N';
    
    public function __construct($id, $size, $available)
    {
        $this->id = $id;
        $this->size = $size;
        $this->available = ($available && $available != 'N') ? 'Y' : 'N';
    }
}
