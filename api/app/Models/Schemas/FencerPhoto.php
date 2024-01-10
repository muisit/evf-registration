<?php

namespace App\Models\Schemas;

use App\Models\Fencer as BaseModel;

/**
 * Registration model
 *
 * @OA\Schema()
 */
class FencerPhoto
{
    /**
     * Unique fencer id
     *
     * @var int
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Status of the photo ID
     *
     * @var string
     * @OA\Property()
     */
    public ?string $photoStatus = null;

    public function __construct(BaseModel $fencer)
    {
        $this->id = $fencer->getKey();
        $this->photoStatus = $fencer->fencer_picture;
    }
}
