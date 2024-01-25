<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class DatesElement extends TextElement
{
    public function generate($el)
    {
        $this->parse($el);
        $txt = $this->data?->dates ?? [];
        if (isset($el->onedateonly) && $el->onedateonly === true) {
            $txt = [$txt[0]];
        }
        $txt = implode("\n", $txt);
        //$txt = str_replace(" ", "~", $txt);
        //$this->replaceTilde = true;
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
