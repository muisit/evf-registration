<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class Element
{
    protected $generator;
    protected $colour;
    protected $size;
    protected $offset;
    protected $ratio;

    protected $data;

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function options() {
        return (object)[
            "colour" => $this->colour ?? '#000000',
            "size" => $this->size ?? [0,0],
            "offset" => $this->offset ?? [0,0],
            "ratio" => $this->ratio ?? 1.0
        ];
    }

    public function withData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function parse($element)
    {
        if (isset($element->style)) {
            if (isset($element->style->color)) {
                $this->colour = $this->parseColour($element->style->color);
            }
            else if (isset($element->style->backgroundColor)) {
                $this->colour = $this->parseColour($element->style->backgroundColor);
            }

            $this->size = $this->parseSize($element);
            $this->offset = $this->parseOffset($element);
        }
    }

    private function parseOffset($element)
    {
        $x = 0;
        $y = 0;
        if (isset($element->style->top)) $y = floatval($element->style->top);
        if (isset($element->style->left)) $x = floatval($element->style->left);

        $x = floatval($x * PDFGenerator::PDF_WIDTH / PDFGenerator::APP_WIDTH);
        $y = floatval($y * PDFGenerator::PDF_HEIGHT / PDFGenerator::APP_HEIGHT);
        if (is_nan($x) || is_nan($y)) {
            return null;
        }
        return [$x, $y];
    }

    private function parseSize($element)
    {
        $x = 0;
        $y = 0;
        if (isset($element->style->height)) $y = floatval($element->style->height);
        if (isset($element->style->width)) $x = floatval($element->style->width);
        if ($x === 0 && $y === 0) return null;

        if (isset($element->ratio)) {
            $this->ratio = floatval($element->ratio);

            \Log::debug("adjusting $x and $y based on $this->ratio");
            if ($x < 1 && $y > 1) {
                $x = $y * $this->ratio;
            }
            if ($y < 1 && $x > 1 && $this->ratio > 0.0) {
                $y = $x / $this->ratio;
            }
        }

        $x = floatval($x * PDFGenerator::PDF_WIDTH / PDFGenerator::APP_WIDTH);
        $y = floatval($y * PDFGenerator::PDF_HEIGHT / PDFGenerator::APP_HEIGHT);
        if (is_nan($x) || is_nan($y)) {
            return null;
        }
        return [$x, $y];
    }

    private function parseColour($colour = "#000000")
    {
        if (strpos($colour, '#') === 0) {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) !== 6 && strlen($colour) !== 3) {
            $colour = "000000";
        }
        $r = hexdec($colour[0]);
        $g = hexdec($colour[1]);
        $b = hexdec($colour[2]);
        if (strlen($colour) == 6) {
            $r = hexdec(substr($colour, 0, 2));
            $g = hexdec(substr($colour, 2, 2));
            $b = hexdec(substr($colour, 4, 2));
        }
        else {
            $r = (16 * $r) + $r;
            $g = (16 * $g) + $g;
            $b = (16 * $b) + $b;
        }
        return array($r, $g, $b);
    }

    protected function getFontFile($family)
    {
        $ffile = base_path(PDFGenerator::FONTPATH . "/$family.ttf");
        if (!file_exists($ffile)) {
            $ffile = resource_path("fonts/$family.ttf");
        }
        if (!file_exists($ffile)) {
            return $this->getFontFile("arial");
        }
        return $ffile;
    }
}
