<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="login",
 *     required=true,
 *     description="Login request parameters",
 *     @OA\JsonContent(ref="#/components/schemas/LoginRequest")
 * )
 */
class LoginRequestBody
{
}

/**
 * Login request model
 *
 * @OA\Schema()
 */
class LoginRequest
{
    /**
     * Username
     * 
     * @var string
     * @OA\Property()
     */
    public string $username;

    /**
     * Password
     * 
     * @var string
     * @OA\Property()
     */
    public string $password;
}
