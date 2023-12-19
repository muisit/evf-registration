<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class Box
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el)
    {
        $this->parse($el);
        $this->generator->pdf->Rect(
            $this->offset[0],
            $this->offset[1],
            $this->size[0],
            $this->size[1],
            "F",
            ["all" => 0],
            $this->colour
        );
    }
}
