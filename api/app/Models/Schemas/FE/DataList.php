<?php

namespace App\Models\Schemas\FE;

use App\Models\Category;
use App\Models\Event;
use App\Models\Weapon;
use App\Models\Fencer as Model;
use App\Models\Ranking;
use App\Models\Registration;
use DateTimeImmutable;

class DataList
{
    public $success = true;
    public $data = null;

    public function __construct($data, $total)
    {
        $this->data = [
            "total" => $total,
            "list" => $data
        ];
    }
}
