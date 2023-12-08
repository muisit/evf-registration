<?php

namespace App\Models\Schemas;

use Illuminate\Database\Eloquent\Model;

/**
 * AccreditationsOverview row model
 *
 * @OA\Schema()
 */
class AccreditationsOverview
{
    /**
     * Type of overview (C=Country, E=Event, R=Role, T=Template)
     *
     * @var integer
     * @OA\Property()
     */
    public string $type;

    /**
     * ID of the country, event, role or template
     *
     * @var integer
     * @OA\Property()
     */
    public string $country;

    /**
     * List of count values
     * @var int[]
     * @OA\Property(
     *   type="array",
     *   @OA\Items(type="int")
     * )
     */
    public array $counts;

    /**
     * List of document information
     * @var Document[];
     * 
     * @OA\Property()
     */
    public array $documents;

    public function __construct(string $type, string $id, array $counts, array $documents)
    {
        $this->type = $type;
        $this->id = $id;
        $this->counts = $counts;
        $this->documents = $documents;
    }
}
