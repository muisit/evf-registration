<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class FontManager
{
    // this factor determines the maximum difference between the width determined by
    // adding up all individual character widths and the final width of the entire
    // string
    const TEXTWIDTH_TO_MM = 0.751;

    const PDF_FONTS = [
        "Courier" => ['correction' => 1, 'key' => "courier"],
        "Courier Italic" => ['correction' => 1, 'key' => "courierI"],
        "Courier Bold" => ['correction' => 1, 'key' => "courierB"],
        "Courier Bold Italic" => ['correction' => 1, 'key' => "courierBI"],
        "DejaVuSans" => ['correction' => 1, 'key' => "dejavusans"],
        "DejaVuSans Italic" => ['correction' => 1, 'key' => "dejavusansI"],
        "DejaVuSans Bold" => ['correction' => 1, 'key' => "dejavusansB"],
        "DejaVuSans Bold Italic" => ['correction' => 1.005, 'key' => "dejavusansBI"],
        "DejaVuSans Condensed" => ['correction' => 1, 'key' => "dejavusanscondensed"],
        "DejaVuSans Condensed Italic" => ['correction' => 1, 'key' => "dejavusanscondensedI"],
        "DejaVuSans Condensed Bold" => ['correction' => 1, 'key' => "dejavusanscondensedB"],
        "DejaVuSans Condensed Bold Italic" => ['correction' => 1, 'key' => "dejavusanscondensedBI"],
        "DejaVuSans Mono" => ['correction' => 1, 'key' => "dejavusansmono"],
        "DejaVuSans Mono Italic" => ['correction' => 1, 'key' => "dejavusansmonoI"],
        "DejaVuSans Mono Bold" => ['correction' => 1, 'key' => "dejavusansmonoB"],
        "DejaVuSans Mono Bold Italic" => ['correction' => 1, 'key' => "dejavusansmonoBI"],
        "Eurofurence" => ['correction' => 1.0304, 'key' => "eurofurence"],
        "Eurofurence Italic" => ['correction' => 1.0304, 'key' => "eurofurenceI"],
        "Eurofurence Bold" => ['correction' => 1.0304, 'key' => "eurofurenceB"],
        "Eurofurence Bold Italic" => ['correction' => 1.0304, 'key' => "eurofurenceBI"],
        "Eurofurence Light" => ['correction' => 1.04339, 'key' => "eurofurencelight"],
        "Eurofurence Light Italic" => ['correction' => 1.045, 'key' => "eurofurencelightI"],
        "FreeMono" => ['correction' => 1, 'key' => "freemono"],
        "FreeMono Italic" => ['correction' => 1, 'key' => "freemonoI"],
        "FreeMono Bold" => ['correction' => 1, 'key' => "freemonoB"],
        "FreeMono Bold Italic" => ['correction' => 1, 'key' => "freemonoBI"],
        "FreeSans" => ['correction' => 1.112, 'key' => "freesans"],
        "FreeSans Italic" => ['correction' => 1.098, 'key' => "freesansI"],
        "FreeSans Bold" => ['correction' => 1.08, 'key' => "freesansB"],
        "FreeSans Bold Italic" => ['correction' => 1.083, 'key' => "freesansBI"],
        "FreeSerif" => ['correction' => 1.04384, 'key' => "freeserif"],
        "FreeSerif Italic" => ['correction' => 1.049, 'key' => "freeserifI"],
        "FreeSerif Bold" => ['correction' => 1.04384, 'key' => "freeserifB"],
        "FreeSerif Bold Italic" => ['correction' => 1.04384, 'key' => "freeserifBI"],
        "Helvetica" => ['correction' => 1, 'key' => "helvetica"],
        "Helvetica Italic" => ['correction' => 1, 'key' => "helveticaI"],
        "Helvetica Bold" => ['correction' => 1, 'key' => "helveticaB"],
        "Helvetica Bold Italic" => ['correction' => 1, 'key' => "helveticaBI"],
        "Times" => ['correction' => 1.005, 'key' => "times"],
        "Times Italic" => ['correction' => 1.005, 'key' => "timesI"],
        "Times Bold" => ['correction' => 1.005, 'key' => "timesB"],
        "Times Bold Italic" => ['correction' => 1.005, 'key' => "timesBI"],
    ];

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function add($fontname)
    {
        if (isset(self::PDF_FONTS[$fontname])) {
            $fontkey = self::PDF_FONTS[$fontname]['key'];

            switch ($fontkey) {
                // our fonts
                case 'eurofurence':
                    $this->generator->pdf->AddFont("Eurofurence", "", resource_path('fonts/eurof55.php'), true);
                    break;
                case 'eurofurenceI':
                    $this->generator->pdf->AddFont("Eurofurence", "I", resource_path('fonts/eurof56.php'), true);
                    break;
                case 'eurofurenceB':
                    $this->generator->pdf->AddFont("Eurofurence", "B", resource_path('fonts/eurof75.php'), true);
                    break;
                case 'eurofurenceBI':
                    $this->generator->pdf->AddFont("Eurofurence", "BI", resource_path('fonts/eurof76.php'), true);
                    break;
                case 'eurofurencelight':
                    $this->generator->pdf->AddFont("Eurofurencelight", "", resource_path('fonts/eurof35.php'), true);
                    break;
                case 'eurofurencelightI':
                    $this->generator->pdf->AddFont("Eurofurencelight", "I", resource_path('fonts/eurof36.php'), true);
                    break;

                // core fonts
                case "courier":
                case "courierB":
                case "courierI":
                case "courierBI":
                case "helvetica":
                case "helveticaB":
                case "helveticaI":
                case "helveticaBI":
                case "times":
                case "timesB":
                case "timesI":
                case "timesBI":
                case "symbol":
                case "zapfdingbats":
            
                // other fonts also available in the TCPDF font folder
                case "dejavusans":
                case "dejavusansI":
                case "dejavusansB":
                case "dejavusansBI":
                case "dejavusanscondensed":
                case "dejavusanscondensedI":
                case "dejavusanscondensedB":
                case "dejavusanscondensedBI":
                case "dejavusansmono":
                case "dejavusansmonoI":
                case "dejavusansmonoB":
                case "dejavusansmonoBI":
                case "freemono":
                case "freemonoI":
                case "freemonoB":
                case "freemonoBI":
                case "freesans":
                case "freesansI":
                case "freesansB":
                case "freesansBI":
                case "freeserif":
                case "freeserifI":
                case "freeserifB":
                case "freeserifBI":
                    $this->generator->pdf->AddFont($fontkey, "", "", true);
                    break;
                default:
                    \Log::error("PDF: font set, but not configured: $fontkey / $fontname");
                    $fontkey = "helvetica";
                    break;
            }
            $this->generator->pdf->SetFont($fontkey);
        }
        else {
            \Log::error("PDF: no such font $fontname");
        }
    }

    public function determineFontSize($text, $size, $font, $fitText)
    {
        $factor = isset(self::PDF_FONTS[$font]) ? self::PDF_FONTS[$font]['correction'] : 1.0;
        // because fontsize is concerned with the height of the font and we want to
        // steer on the width of the font, we need to convert the actual text
        // to a font-size that matches the expected width as configured for the
        // default Helvetica font
        if ($font == "helvetica") {
            $newsize = $size;
        }
        else {
            $this->generator->pdf->SetFontSize($size);
            $this->generator->pdf->SetFont("helvetica");
            $textwidthhelvetica = $this->getTextWidth($text);

            $newsize = $size;
            $this->add($font);
            while (true) {
                $this->generator->pdf->SetFontSize($newsize);
                $this->add($font);
                $fontwidth = $this->getTextWidth($text);

                if (abs(($factor * $fontwidth) - $textwidthhelvetica) < 1) {
                    break;
                }

                $widthratio = $textwidthhelvetica / ($factor * $fontwidth);
                $newsize = $newsize * $widthratio;
            }
        }

        if ($fitText !== null && $fitText > 0) {
            $maxsize = $newsize; // do not increase the size
            while (true) {
                $this->generator->pdf->SetFontSize($newsize);
                $this->add($font);
                $fontwidth = $this->getTextWidth($text);

                // if the font size is already small enough, it is fine
                if ($newsize == $maxsize && ((self::TEXTWIDTH_TO_MM * $factor * $fontwidth) - $fitText) <= 0) {
                    break;
                }
    
                if (abs((self::TEXTWIDTH_TO_MM * $factor * $fontwidth) - $fitText) < 1 && $newsize <= $maxsize) {
                    break;
                }

                $widthratio = $fitText / (self::TEXTWIDTH_TO_MM * $factor * $fontwidth);
                $newsize = $newsize * $widthratio;

                // do not increase the font-size to win a few pixels
                if ($newsize > $maxsize) {
                    $newsize = $maxsize;
                    break;
                }
            }
        }

        return $newsize;
    }

    private function getTextWidth($txt)
    {
        return floatval($this->generator->pdf->GetStringWidth($txt));
    }
}
