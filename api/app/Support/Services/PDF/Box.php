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

    public function generate($el, $content, $data)
    {
    }
}
