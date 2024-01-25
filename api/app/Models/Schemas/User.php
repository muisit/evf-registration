<?php

namespace App\Models\Schemas;

use App\Models\WPUser;

/**
 * Event Role information model
 *
 * @OA\Schema()
 */
class User
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
    public string $name;

    public function __construct(WPUser $user)
    {
        $this->id = $user->getKey();
        $this->name = $user->display_name;
    }
}
