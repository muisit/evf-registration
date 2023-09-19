<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="registration",
 *     required=true,
 *     description="Registration data",
 *     @OA\JsonContent(ref="#/components/schemas/Registration")
 * )
 */
class Registration
{
}
