<?php

require __DIR__ . '/libcode.php';

$start = intval($argv[1] ?? 1);
$end = intval($argv[2] ?? 999);
$event = intval($argv[3] ?? 37);
$codes = createDocumentCodes($start, $end, $event);

#require __DIR__ . '/defaultDocument.php';
require __DIR__ . '/cineyDocument.php';
$params = documentParameters();
echo $params['template'];

$positionCode1 = $params['code1'] ?? [];
$positionCode2 = $params['code2'] ?? [];
$fontsizeText = $params['fontsize'] ?? 20;
$positionText1 = $params['text1'] ?? [];
$positionText2 = $params['text2'] ?? [];

$index = 0;
foreach ($codes as $codeData) {
    $code = $codeData['code'];
    $nr = sprintf("%04d", $codeData['document']);
    if ($index > 0) {
        echo "page\r\n";
    }

    echo "template print doc 0 0\r\n";

    $x = $positionCode1[0];
    $y = $positionCode1[1];
    $codeHeight = $positionCode1[2];
    $codeWidth = $positionCode1[3];
    echo "barcode $x $y $codeHeight $codeWidth \"$code\" 1d\r\n";

    if (isset($positionCode2) && count($positionCode2) == 4) {
        $x = $positionCode2[0];
        $y = $positionCode2[1];
        $codeHeight = $positionCode2[2];
        $codeWidth = $positionCode2[3];
        echo "barcode $x $y $codeHeight $codeWidth \"$code\" 1d\r\n";
    }

    echo "fontsize $fontsizeText\r\n";
    $x = $positionText1[0];
    $y = $positionText1[1];
    echo "text $x $y \"$code\"\r\n";

    if (isset($positionText2) && count($positionText2) == 2) {
        $x = $positionText2[0];
        $y = $positionText2[1];
        echo "text $x $y \"$code\"\r\n";
    }

    $index++;
}

echo "save \"output\"\r\n\r\n";
