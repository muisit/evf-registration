<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class CountryElement extends TextElement
{
    public function generate($el)
    {
        $this->parse($el);
        $txt = $this->data?->country ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
