<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class TextElement extends Element
{
    protected $fontSize;
    protected $fontFamily;
    protected $alignment;
    protected $wrap;
    protected $replaceTilde;
    protected $fitText = false;
    public $outOfTemplate = false;

    public function parse($element)
    {
        parent::parse($element);
        if (isset($element->style)) {
            $this->fontSize = $this->parseFontSize($element);
            $this->fontFamily = $this->parseFontFamily($element);
            $this->alignment = $this->parseAlignment($element);
        }
        if (isset($element->fitText) && $element->fitText === true) {
            $this->fitText = true;
        }
    }

    public function options()
    {
        $obj = parent::options();
        $obj->fontSize = $this->fontSize ?? 20;
        $obj->fontFamily = $this->fontFamily ?? 'Helvetica';
        $obj->alignment = $this->alignment ?? '';
        $obj->wrap = $this->wrap ?? true;
        $obj->replaceTilde = $this->replaceTilde ?? false;
        $obj->fitText = $this->fitText ?? false;
        return $obj;
    }

    private function parseFontSize($element)
    {
        if (isset($element->style->fontSize)) {
            return intval($element->style->fontSize);
        }
        return 20;
    }

    private function parseFontFamily($element)
    {
        $family = "Helvetica";
        if (isset($element->style->fontFamily)) {
            $family = $element->style->fontFamily;
            if (!in_array($family, array_keys(FontManager::PDF_FONTS))) {
                $family = "Helvetica";
            }
        }
        return $family;
    }

    private function parseAlignment($element)
    {
        $align = '';
        if (isset($element->style->textAlign)) {
            $ta = $element->style->textAlign;
            switch ($ta) {
                case 'center':
                    $align = 'C';
                    break;
                case 'right':
                    $align = 'R';
                    break;
                case 'justify':
                    $align = 'J';
                    break;
                default:
                    $align = '';
                    break;
            }
        }
        return $align;
    }

    public function generate($el)
    {
        $this->parse($el);
        $txt = $el->text ?? '';
        if (strlen(trim($txt))) {
            $this->insertText($txt);
        }
    }

    private function addFont($fontfamily)
    {
        (new FontManager($this->generator))->add($fontfamily);
    }

    protected function insertText($text)
    {
        if (isset($this->colour) && $this->colour != "") {
            $this->generator->pdf->SetTextColorArray($this->colour);
        }
        $this->generator->pdf->setTextRenderingMode($stroke = 0, $fill = true, $clip = false);
        $x = 0;
        $y = 0;
        $width = PDFGenerator::PDF_WIDTH;
        $height = PDFGenerator::PDF_HEIGHT;
        if (isset($this->offset)) {
            $x = $this->offset[0];
            $y = $this->offset[1];
            $width = $width - $x;
            $height = $height - $y;
        }
        if (isset($this->size)) {
            $swidth = $this->size[0];
            $sheight = $this->size[1];
            if ($swidth < $width || $this->outOfTemplate) {
                $width = $swidth;
            }
            if ($sheight < $height || $this->outOfTemplate) {
                $height = $sheight;
            }
        }

        $font = $this->fontFamily ?? "helvetica";
        if ($font != "helvetica") {
            $this->addFont($font);
        }

        $fontsize = (new FontManager($this->generator))->determineFontSize($text, $this->fontSize, $font, $this->fitText ? $this->size[0] : null);
        $this->addFont($font);
        $this->generator->pdf->SetFontSize($fontsize * PDFGenerator::PDF_PXTOPT);

        $lineheight = $this->generator->pdf->getCellHeight($this->generator->pdf->GetFontSize());
        $fontwidth = floatval($this->generator->pdf->GetStringWidth($text));
        $align = $this->alignment ?? '';
        //$this->pdf->Rect($x, $y - 0.5, $fontwidth,$lineheight, "B",array("all"=>0.5),array(128,0,128));

        $lines = $this->breakText($text, $width);
        $maxlines = intval(floor($height / $lineheight)) + 1; // allow the last line to overflow (a bit)

        // Print at least 1 line, even if it overflows.
        // This allows us to set a very small height and make sure exactly one line is printed
        if ($maxlines < 1) $maxlines = 1;
        if (isset($this->wrap) && $this->wrap === false && count($lines) > 1) {
            if ($fontsize > 6) {
                $this->fontSize = $this->fontSize - 1;
            }
            else {
                // too much text, wrap anyway and cause an overflow
                $this->wrap = true;
            }
            return $this->insertText($text);
        }

        if ($maxlines < sizeof($lines)) {
            // cut off lines we cannot print
            $lines = array_slice($lines, 0, $maxlines);
        }

        $offset = -0.5;
        foreach ($lines as $line) {
            $this->generator->pdf->SetXY($x, $y + $offset);
            $offset += $lineheight;
            $line = $this->replaceTildeCharacter($line); // a cheap version of non-breaking-spaces
            $this->generator->pdf->Cell(
                $width,
                $lineheight,
                $txt = $line,
                $border = 0,
                $ln = 0,
                $align,
                $fill = false,
                $link = '',
                $stretch = 0,
                $ignore_min_height = false,
                $calign = 'T',
                $valign = 'T'
            );
        }
    }

    private function replaceTildeCharacter($text)
    {
        if (isset($this->replaceTilde) && $this->replaceTilde === true) {
            return str_replace("~", " ", $text);
        }
        return $text;
    }

    

    private function breakText($text, $width)
    {
        // break the text into pieces based on whitespace, comma, dot and hyphen separation
        $tokens = $this->breakTextIntoTokens($text);
        $pdf = $this->generator->pdf;
        $sizes = array_map(function ($item) use ($pdf) {
            $letters = preg_split('//u', $item, null, PREG_SPLIT_NO_EMPTY);
            $size = 0;
            for ($i = 0; $i < sizeof($letters); $i++) {
                $size += $pdf->GetCharWidth(ord($letters[$i]));
            }
            return $size;
        }, $tokens);

        $lws = $pdf->GetCharWidth(" ");
        $lines = array();
        $current = 0;
        $line = "";
        for ($i = 0; $i < sizeof($tokens); $i++) {
            $token = $tokens[$i];
            $size = $sizes[$i];
            if ($token == "\n") {
                // line break, start a new line
                if (strlen($line)) {
                    $lines[] = $line;
                }
                $line = "";
                $current = 0;
            }
            else if (($current + ($current > 0 ? $lws : 0) + $size) > $width) {
                // new line
                if (strlen($line)) {
                    $lines[] = $line;
                }
                $line = $token;
                $current = $size;
            }
            else {
                if (mb_strlen($line) > 0) {
                    $line .= " ";
                    $current += $lws;
                }
                $line .= $token;
                $current += $size;
            }
        }
        if (mb_strlen($line) > 0) {
            $lines[] = $line;
        }
        return $lines;
    }

    private function breakTextIntoTokens($text)
    {
        // we could do a complicated regexp, but instead we just run over the text
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $totalsize = sizeof($characters);
        $retval = [];
        $current = "";
        for ($i = 0; $i < $totalsize; $i++) {
            $c = $characters[$i];
            $n = ($i < ($totalsize - 1)) ? $characters[$i + 1] : "\n";
            $isspace = preg_match('/\s/u', $c);
            $ispunc = preg_match('/\p{P}/u', $c);
            $nextspace = preg_match('/\s/u', $n);
            $islinebreak = (mb_ord($c) == 10);

            if ($islinebreak) {
                // we must keep the line breaks in order to actually break lines later on
                if (mb_strlen($current) > 0) {
                    $retval[] = $current;
                    $current = "";
                }
                $retval[] = "\n";
            }
            else if ($isspace) {
                if (mb_strlen($current) > 0) {
                    $retval[] = $current;
                    $current = "";
                }
                // skip any whitespace, it is converted to a single space
            }
            // punctuation should always be followed by a space, or else
            // it is part of a token (@-sign, dots in addresses, etc.)
            else if ($ispunc && $nextspace) {
                // punctuation belongs to the current value
                if (mb_strlen($current) > 0) {
                    $current .= $c;
                }
                else {
                    $current = $c;
                }
                $retval[] = $current;
                $current = "";
            }
            else {
                $current .= $c;
            }
        }
        if (mb_strlen($current) > 0) {
            $retval[] = $current;
        }
        return $retval;
    }

    public function replaceTildeForSpaces($value = true)
    {
        $this->replaceTilde = $value;
    }
}
