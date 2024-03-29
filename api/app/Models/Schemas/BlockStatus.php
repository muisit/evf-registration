<?php

namespace App\Models\Schemas;

class BlockStatus
{
    public int $count = 0;
    public string $last = '';

    public function __construct(int $c, string $l)
    {
        $this->count = $c;
        $this->last = $l;
    }
}
