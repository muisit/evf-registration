<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class CountryElement
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $txt = $data->country ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
