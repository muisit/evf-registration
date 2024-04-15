<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="follow",
 *     required=true,
 *     description="Follow request parameters",
 *     @OA\JsonContent(ref="#/components/schemas/FollowRequest")
 * )
 */
class FollowRequestBody
{
}

/**
 * Follow request model
 *
 * @OA\Schema()
 */
class FollowRequest
{
    /**
     * UUID of the fencer for which this request is concerned
     * 
     * @var string
     * @OA\Property()
     */
    public string $fencer;

    /**
     * Settings
     * 
     * @var string
     * @OA\Property(type="array",
     *   @OA\Items(type="string"))
     */
    public array $settings;
}
