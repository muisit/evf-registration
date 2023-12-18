<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class AccreditationID
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($options)
    {
        // putAccId
    }
}