<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class RolesElement extends TextElement
{
    public function generate($el)
    {
        $this->parse($el);
        $txt = $this->data?->roles ?? [];
        $txt = implode(", ", $txt);
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }
}
