<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class RolesElement
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $txt = $data->roles ?? [];
        $txt = implode(", ", $txt);
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
