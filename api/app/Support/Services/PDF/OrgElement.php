<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class OrgElement extends TextElement
{
    public function generate($el)
    {
        $this->parse($el);
        $txt = $this->data?->organisation ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
