<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="block",
 *     required=true,
 *     description="Block request parameters",
 *     @OA\JsonContent(ref="#/components/schemas/Block")
 * )
 */
class BlockBody
{
}

/**
 * Block request model
 *
 * @OA\Schema()
 */
class Block
{
    /**
     * UUID of the user to block
     * 
     * @var string
     * @OA\Property()
     */
    public string $id;

    /**
     * Block value, Y or N
     *
     * @var string
     * @OA\Property()
     */
    public string $block;
}
