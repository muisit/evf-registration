<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class CountryFlag extends BasicImage
{
    private $generator;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function generate($el, $data)
    {
        $this->parse($el);
        $fpath = $data->country_flag ?? null;
        if (empty($fpath)) return;

        $fpath = basepath($fpath);
        if (!file_exists($fpath)) return;

        if (!isset($this->size)) {
            list($width, $height) = getimagesize($path);
            $this->size = [$width, $height];
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
        $this->insertImage($path);
    }
}
