<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="fencer",
 *     required=true,
 *     description="Fencer data",
 *     @OA\JsonContent(ref="#/components/schemas/Fencer")
 * )
 */
class Fencer
{
}
