<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class DatesElement
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $txt = $data->dates ?? [];
        if (isset($el->onedateonly) && $el->onedateonly === true) {
            $txt = [$txt[0]];
        }
        $txt = implode("\n", $txt);
        $txt = implode("\n", str_replace(" ", "~", $txt));
        $this->replaceTilde = true;
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
