<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;
use App\Models\Device as BaseModel;

/**
 * Device model
 *
 * @OA\Schema()
 */
class Device
{
    /**
     * ID of the device
     *
     * @var string
     * @OA\Property()
     */
    public ?string $id = null;

    public function __construct(?BaseModel $device = null)
    {
        if (!empty($device)) {
            $this->id = $device->uuid;
        }
    }
}
