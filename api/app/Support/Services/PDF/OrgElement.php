<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class OrgElement
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $txt = $data->organisation ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
