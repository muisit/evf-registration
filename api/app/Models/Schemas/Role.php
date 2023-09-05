<?php

namespace App\Models\Schemas;

use App\Models\Role as BaseModel;

/**
 * Role model
 *
 * @OA\Schema()
 */
class Role
{
    /**
     * ID of the role
     *
     * @var integer
     * @OA\Property()
     */
    public ?int $id = null;

    /**
     * Name of the role
     *
     * @var string
     * @OA\Property()
     */
    public ?string $name = null;

    /**
     * Type name of the role
     *
     * @var $string
     * @OA\Property()
     */
    public ?string $type = null;

    public function __construct(?BaseModel $data = null)
    {
        if (!empty($data)) {
            $this->id = $data->getKey();
            $this->name = $data->role_name;
            $this->type = $data->type->org_declaration;
        }
    }
}
