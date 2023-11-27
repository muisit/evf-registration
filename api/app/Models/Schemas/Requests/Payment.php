<?php

namespace App\Models\Schemas\Requests;

/**
 * @OA\RequestBody(
 *     request="payment",
 *     required=true,
 *     description="Payment data",
 *     @OA\JsonContent(ref="#/components/schemas/Payment")
 * )
 */
class Payment
{
}
