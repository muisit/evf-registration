<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class BasicImage extends Element
{
    protected function insertImage($path)
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, ["png", "jpg", "jpeg", "gif"])) {
            return;
        }

        list($width, $height) = getimagesize($path);
        $x = 0;
        $y = 0;
        if (isset($this->offset)) {
            $x = $this->offset[0];
            $y = $this->offset[1];
        }
        if (isset($this->size)) {
            $swidth = $this->size[0];
            $sheight = $this->size[1];
            if ($swidth < $width) {
                // adjusting image width $width to smaller width $swidth based on size
                $width = $swidth;
            }
            if ($sheight < $height) {
                // adjusting image height $height to smaller height $sheight based on size
                $height = $sheight;
            }
        }
        $this->generator->pdf->setJPEGQuality(90);
        $this->generator->pdf->Image($path, $x, $y, $width, $height, $type = '', $link = '', $align = '', $resize = true, $dpi = 600, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = 'CM', $hidden = false, $fitonpage = false, $alt = false, $altimgs = []);
    }
}
