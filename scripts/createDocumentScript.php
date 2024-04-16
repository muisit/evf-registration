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

$index = 0;
foreach ($codes as $codeData) {
    if ($index > 0) {
        echo "page\r\n";
    }
    outputDocumentCode($index, $codeData, $params);

    $index++;
}

echo "save \"output\"\r\n\r\n";
