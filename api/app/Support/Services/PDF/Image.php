<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class Image extends BasicImage
{
    public function generate($el, $pictures)
    {
        $this->parse($el);
        $imageid = $element->file_id ?? '';

        if (isset($pictures[$imageid])) {
            $pic = $pictures[$imageid];
            $ext = $pic["file_ext"];
            if (isset($pic["path"])) {
                $path = $pic["path"];
            }
            else {
                $path = $this->generator->accreditation->template->image($imageid, $ext);
            }

            // correct width/height downwards according to ratio
            if (isset($this->ratio) && $this->ratio > 0.0) {
                $rwidth = $this->size[1] * $this->ratio;
                $rheight = $this->size[0] / $this->ratio;

                if ($rwidth < $this->size[0]) {
                    $this->size[0] = $rwidth;
                }
                else if ($rheight < $this->size[1]) {
                    $this->size[1] = $rheight;
                }
            }

            if (file_exists($path)) {
                $this->insertImage($path);
            }
        }
    }
}
