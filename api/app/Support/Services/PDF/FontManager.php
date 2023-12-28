<?php

namespace App\Support\Services\PDF;

use App\Support\Services\PDFGenerator;

class FontManager
{
    const PDF_FONTS = [
        "Courier" => "courier",
        "Courier Italic" => "courierI",
        "Courier Bold" => "courierB",
        "Courier Bold Italic" => "courierBI",
        "DejaVuSans" => "dejavusans",
        "DejaVuSans Italic" => "dejavusansI",
        "DejaVuSans Bold" => "dejavusansB",
        "DejaVuSans Bold Italic" => "dejavusansBI",
        "DejaVuSans Condensed" => "dejavusanscondensed",
        "DejaVuSans Condensed Italic" => "dejavusanscondensedI",
        "DejaVuSans Condensed Bold" => "dejavusanscondensedB",
        "DejaVuSans Condensed Bold Italic" => "dejavusanscondensedBI",
        "DejaVuSans Mono" => "dejavusansmono",
        "DejaVuSans Mono Italic" => "dejavusansmonoI",
        "DejaVuSans Mono Bold" => "dejavusansmonoB",
        "DejaVuSans Mono Bold Italic" => "dejavusansmonoBI",
        "Eurofurence" => "eurofurence",
        "Eurofurence Italic" => "eurofurenceI",
        "Eurofurence Bold" => "eurofurenceB",
        "Eurofurence Bold Italic" => "eurofurenceBI",
        // there seems to be a PDF problem with the regular Eurofurence Light, so it is disabled for now
        "Eurofurence Light" => "eurofurencelight",
        "Eurofurence Light Italic" => "eurofurencelightI",
        "FreeMono" => "freemono",
        "FreeMono Italic" => "freemonoI",
        "FreeMono Bold" => "freemonoB",
        "FreeMono Bold Italic" => "freemonoBI",
        "FreeSans" => "freesans",
        "FreeSans Italic" => "freesansI",
        "FreeSans Bold" => "freesansB",
        "FreeSans Bold Italic" => "freesansBI",
        "FreeSerif" => "freeserif",
        "FreeSerif Italic" => "freeserifI",
        "FreeSerif Bold" => "freeserifB",
        "FreeSerif Bold Italic" => "freeserifBI",
        "Helvetica" => "helvetica",
        "Helvetica Italic" => "helveticaI",
        "Helvetica Bold" => "helveticaB",
        "Helvetica Bold Italic" => "helveticaBI",
        "Times" => "times",
        "Times Italic" => "timesI",
        "Times Bold" => "timesB",
        "Times Bold Italic" => "timesBI",
    ];

    public function __construct(PDFGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function add($fontname)
    {
        if (isset(self::PDF_FONTS[$fontname])) {
            $fontkey = self::PDF_FONTS[$fontname];

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
}
