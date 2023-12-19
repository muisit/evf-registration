<?php

namespace App\Support\Services\PDF;

use TCPDF as BasePDF;

class TCPDF extends BasePDF
{
    public function setFileId(string $id)
    {
        $this->file_id = $id;
    }
}
