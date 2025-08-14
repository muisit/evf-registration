<?php

namespace App\Models\Schemas\FE;

use App\Models\Category;
use App\Models\Event;
use App\Models\Weapon;
use App\Models\Fencer as Model;
use App\Models\Ranking;
use App\Models\Registration;
use DateTimeImmutable;

class WPResponse
{
    public $success = true;
    public $data = null;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
