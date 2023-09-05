<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;

/**
 * BasicData model
 *
 * @OA\Schema()
 */
class BasicData
{
    /**
     * Categories, sorted
     * 
     * @var Category[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Category")
     * )
     */
    public ?array $categories = null;

    /**
     * Weapons, sorted
     * 
     * @var Weapon[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Weapon")
     * )
     */
    public ?array $weapons = null;

    /**
     * Roles, sorted
     * 
     * @var Role[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Role")
     * )
     */
    public ?array $roles = null;

    /**
     * Countries, sorted
     * 
     * @var Country[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="Country")
     * )
     */
    public ?array $countries = null;

    public function add(\Countable $items)
    {
        if (count($items) > 0) {
            $schemaClass = null;
            switch (get_class($items[0])) {
                case \App\Models\Country::class:
                    $schemaClass = \App\Models\Schemas\Country::class;
                    break;
                case \App\Models\Role::class:
                    $schemaClass = \App\Models\Schemas\Role::class;
                    break;
                case \App\Models\Weapon::class:
                    $schemaClass = \App\Models\Schemas\Weapon::class;
                    break;
                case \App\Models\Category::class:
                    $schemaClass = \App\Models\Schemas\Category::class;
                    break;
            }

            if (!empty($schemaClass)) {
                $values = [];
                foreach ($items as $item) {
                    $values[] = new $schemaClass($item);
                }

                switch ($schemaClass) {
                    case \App\Models\Schemas\Country::class:
                        $this->countries = $values;
                        break;
                    case \App\Models\Schemas\Role::class:
                        $this->roles = $values;
                        break;
                    case \App\Models\Schemas\Weapon::class:
                        $this->weapons = $values;
                        break;
                    case \App\Models\Schemas\Category::class:
                        $this->categories = $values;
                        break;
                }
            }
        }
    }
}
