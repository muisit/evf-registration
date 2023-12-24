<?php

namespace App\Models\Schemas;

/**
 * Summary input model
 *
 * @OA\Schema()
 */
class Summary
{
    /**
     * type
     *
     * @var string
     * @OA\Property()
     */
    public string $type;

    /**
     * type identifier
     *
     * @var int
     * @OA\Property()
     */
    public int $typeId;
}
