<?php

require_once(__DIR__ . '/../api/vendor/autoload.php');

$configuration = [];
$pdf = null;

for ($i = 1; $i < sizeof($argv); $i++) {
    $content = file_get_contents($argv[$i]);
    parseContent($content);
}

function parseContent($content)
{
    $lines = preg_split("/\r\n|\n|\r/", $content);
    foreach ($lines as $line) {
        if (!empty($line) && $line[0] != '#') {
            interpretLine($line);
        }
    }
}

function interpretLine($line)
{
    global $configuration;
    global $pdf;
    $line = trim($line);
    $tokens = splitIntoTokens($line);
    if (empty($tokens)) {
        return;
    }
    fprintf(STDERR, "$line\r\n");
    switch (strtolower($tokens[0])) {
        case 'output': // output <file>
            $configuration['output'] = $tokens[1];
            break;
        case 'create': // create A4 P
            $page = $configuration['pagesize'] ?? parsePageSize($tokens[1]);
            $orientation = $configuration["orientation"] ?? parseOrientation($tokens[2]);
            /* last parameter: pdfa mode 3 */
            $pdf = new TCPDF($orientation, "mm", $page, true, 'UTF-8', false, 3);
            $pdf->SetDefaultMonospacedFont('courier');
            $pdf->SetFont('helvetica', '', 14, '', true);
            fprintf(STDERR, "    document created\r\n");
            break;
        case 'page':
            $pdf->AddPage();
            break;
        case 'orientation': // orientation landscape
            $configuration['orientation'] = parseOrientation($tokens[1] ?? 'P');
            break;
        case 'pagesize': // pagesize A5
            $configuration['pagesize'] = parsePageSize($tokens[1] ?? 'A4');
            break;
        case 'background':
            $configuration['background'] = parseColour($tokens['1'] ?? '#ffffff');
            break;
        case 'colour':
            $configuration['colour'] = parseColour($tokens['1'] ?? '#000000');
            break;
        case 'box':
            makeBoxAt($tokens);
            break;
        case 'text': // text 1.4 8.2 "This is an example" <lineheight>
            putTextAt($tokens);
            break;
        case 'barcode': // barcode x y width height link type=2d points=1
            putQrCode($tokens);
            break;
        case 'font':
            if ($pdf) {
                if (strlen($tokens[2] ?? '')) {
                    $pdf->AddFont($tokens[1], $tokens[3] ?? "", $tokens[2], true);
                }
                else {
                    $pdf->AddFont($tokens[1] ?? 'helvetica', "", "", true);
                }
                $pdf->SetFont($tokens[1] ?? 'helvetica');
            }
            break;
        case 'fontsize':
            if ($pdf) $pdf->SetFontSize(parseFloat($tokens[1] ?? '10'));
            break;
        case 'lineheight': // lineheight 1.5
            $configuration['lineheight'] = floatval($tokens[1] ?? '0');
            break;
        case 'textalign': // textalign left
            $configuration['textalign'] = parseAlignment($tokens[1] ?? 'L');
            break;
        case 'save': // save "outputme"
            saveDocument($tokens[1] ?? ($configuration['output'] ?? 'output'), $configuration);
            break;
        case 'set': // set <id> <value>
            if (!isset($configuration['values'])) {
                $configuration['values'] = [];
            }
            $configuration['values'][$tokens[1] ?? '-'] = $tokens[2] ?? 0;
            break;
        case 'creator':
            if($pdf) $pdf->setCreator($tokens[1] ?? '');
            break;
        case 'author':
            if($pdf) $pdf->setAuthor($tokens[1] ?? '');
            break;
        case 'title':
            if($pdf) $pdf->setTitle($tokens[1] ?? '');
            break;
        case 'subject':
            if($pdf) $pdf->setSubject($tokens[1] ?? '');
            break;
        case 'keywords':
            if($pdf) $pdf->setKeywords($tokens[1] ?? '');
            break;
        case 'printheader':
            if($pdf) {
                $pdf->setPrintHeader(parseBoolean($tokens[1] ?? ''));
            }
            break;
        case 'printfooter':
            if($pdf) $pdf->setPrintFooter(parseBoolean($tokens[1] ?? ''));
            break;
        case 'margins':
            if($pdf) {
                $pdf->SetMargins(
                        parseFloat($tokens[1] ?? '5'),
                        parseFloat($tokens[2] ?? '5'),
                        parseFloat($tokens[3] ?? '5'),
                        parseBoolean($tokens[4] ?? '')
                );
            }
            break;
        case 'autobreak':
            if($pdf) $pdf->setAutoPageBreak(parseBoolean($tokens[1] ?? ''));
            break;
        case 'imagescale':
            if($pdf) $pdf->setKeywords(parseFloat($tokens[1] ?? '0'));
            break;
        case 'fontsubsetting':
            if($pdf) $pdf->setFontSubsetting(parseBoolean($tokens[1] ?? ''));
            break;
    }
}

function putQRCode($tokens)
{
    // qr x y size
    global $pdf;
    global $configuration;
    if ($pdf) {
        $x = parseFloat($tokens[1] ?? '0');
        $y = parseFloat($tokens[2] ?? '0');
        $width = parseFloat($tokens[3] ?? '0');
        $height = parseFloat($tokens[4] ?? '0');
        $link = $tokens[5] ?? '';
        $type = $tokens[6] ?? 'QRCODE,H';
        $points = parseFloat($tokens[7] ?? '1');

        if ($width > 0 && $height > 0 && $x >= 0 && $y >= 0) {
            $style = [
                'border' => 2,
                'vpadding' => 'auto',
                'hpadding' => 'auto',
                'fgcolor' => $configuration['colour'] ?? [0, 0, 0],
                'bgcolor' => $configuration['background'] ?? [255,255,255],
                'module_width' => $points, // width of a single module in points
                'module_height' => $points // height of a single module in points
            ];

            switch (strtolower($type)) {
                default:
                case '2d':
                    // QRCODE,H : QR-CODE Best error correction
                    $pdf->write2DBarcode(
                        $link,
                        'QRCODE,H',
                        $x,
                        $y,
                        $width,
                        $height,
                        $style,
                        'N'
                    );
                    break;
                case '1d':
                    $pdf->write1DBarcode(
                        $link,
                        'I25+',
                        $x,
                        $y,
                        $width,
                        $height,
                        null, // bar width, default 0.4mm
                        $style,
                        'T' // position box top at the pointer
                    );
                    break;
            }              
        }
    }
}

function parseFloat($value)
{
    global $configuration;
    if (is_numeric($value)) {
        return floatval($value);
    }
    if (isset($configuration["values"]) && isset($configuration["values"][$value])) {
        return floatval($configuration["values"][$value]);
    }
    return 0.0;
}

function saveDocument($filename)
{
    global $pdf;
    if ($pdf) {
        fprintf(STDERR, "    saving document to $filename\r\n");
        if($filename[0] !== DIRECTORY_SEPARATOR) {
            $filename = getcwd() . DIRECTORY_SEPARATOR . $filename;
        }
        $pdf->Output($filename . '.pdf', 'F');
    }
}

function makeBoxAt($tokens)
{
    global $configuration;
    global $pdf;
    if ($pdf) {
        $x = parseFloat($tokens[1] ?? '0');
        $y = parseFloat($tokens[2] ?? '0');
        $width = parseFloat($tokens[3] ?? '0');
        $height = parseFloat($tokens[4] ?? '0');

        if ($width > 0 && $height > 0 && $x >= 0 && $y >= 0) {
            fprintf(STDERR, "    printing box at $x, $y -> $width, $height\r\n");
            $pdf->Rect($x, $y, $width, $height, "F", ["all" => 0], $configuration['background'] ?? '#000000');
        }
    }
}

function putTextAt($tokens)
{
    global $configuration;
    global $pdf;
    if ($pdf) {
        $x = parseFloat($tokens[1]);
        $y = parseFloat($tokens[2]);
        $text = $tokens[3];
        $width = parseFloat($tokens[4] ?? '0');
        $lineheight = $configuration['lineheight'] ?? 0;
        $textalign = $configuration['textalign'] ?? '';
        fprintf(STDERR, "    putting text '$text' at $x, $y\r\n");
        $pdf->SetXY($x, $y);
        $pdf->Cell($width, $lineheight, $text); //, $border = 0, $ln = 0, $textalign, $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'B');
    }
}

function parsePageSize($value)
{
    switch (strtolower($value)) {
        case 'a3': return 'A3';
        case 'a4': return 'A4';
        case 'a5': return 'A5';
        case 'a6': return 'A6';
    }
    return 'A4';
}

function parseOrientation($value)
{
    switch (strtolower($value)) {
        case 'portrait':
        case 'p':
            return 'P';
        case 'landscape':
        case 'l':
            return 'L';
    }
    return 'P';
}

function parseAlignment($value)
{
    switch (strtolower($value)) {
        case 'left':
        case 'start':
        case 'l':
            return 'L';
        case 'right':
        case 'end':
        case 'r':
            return 'R';
        case 'center':
        case 'middle':
        case 'c':
            return 'C';
        case 'top':
        case 't':
            return 'T';
        case 'bottom':
        case 'b':
            return 'B';
    }
    return 'L';
}

function parseBoolean($value)
{
    switch (strtolower($value)) {
        case 'y':
        case 'ye':
        case 'yes':
        case 'on':
        case '1':
            return true;
        default:
            return false;
    }
}

function parseColour($colour)
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

function splitIntoTokens($text)
{
    $tokens = [];
    $chars = mb_str_split($text);
    $current = "";
    $inQuote = false;
    $escaped = false;
    foreach ($chars as $c) {
        if ($escaped) {
            $current .= $c;
            $escaped = false;
        }
        else if (!$inQuote && ($c == '"' || $c == "'") && $current == '') {
            $inQuote = $c;
        }
        else if ($c == '\\') {
            $escaped = true;
        }
        else if($inQuote && $c == $inQuote) {
            $inQuote = false;
        }
        else if (preg_match("/\s/", $c) && !$inQuote) {
            if ($current != '') {
                $tokens[] = $current;
            }
            $current = '';
        }
        else {
            $current .= $c;
        }
    }
    if ($current != '') {
        $tokens[] = $current;
    }
    return $tokens;
}