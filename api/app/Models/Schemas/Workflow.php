<?php

namespace App\Models\Schemas;

use App\Models\Workflow as Model;

/**
 * Workflow Role information model
 *
 * @OA\Schema()
 */
class Workflow
{
    /**
     * Id of the record
     *
     * @var int
     * @OA\Property()
     */
    public int $id;

    /**
     * User name
     *
     * @var int
     * @OA\Property()
     */
    public array $sandbox;

    public function __construct(Model $flow)
    {
        $this->id = $flow->getKey();
        $this->sandbox = $flow->sandbox;
    }
}
