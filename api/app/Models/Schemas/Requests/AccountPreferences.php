<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="preferences",
 *     required=true,
 *     description="Account Preference request parameters",
 *     @OA\JsonContent(ref="#/components/schemas/Preferences")
 * )
 */
class AccountPreferenceBody
{
}

/**
 * AccountPreference
 *
 * @OA\Schema()
 */
class AccountPreference
{
    /**
     * List of settings for accounts we are following
     * 
     * @var string
     * @OA\Property()
     */
    public array $following;

    /**
     * List of settings for accounts that follow us
     *
     * @var string
     * @OA\Property()
     */
    public array $followers;
}
