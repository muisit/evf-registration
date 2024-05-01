<?php

require __DIR__ . '/libcode.php';

$codes = [];
for ($i = 1; $i < count($argv); $i++) {
    $codes[] = $argv[$i];
}

echo <<< HEREDOC
create A4 P
printheader no
printfooter no
creator "Muis IT"
author "Muis IT"
title "Codes"
margins 0 0 0
font helvetica
fontsize 70
border all #000000 0.5
background #ffffff
page

HEREDOC;

$index = 0;
foreach ($codes as $code) {
    $y = 15 + ($index * 40);
    $by = $y + 5;
    $ty = $y + 11;

    $typecode = $code[2];
    $type = 'unknown';
    switch ($typecode) {
        case '0': $type = 'Admin'; break;
        case '1': $type = 'Accreditation'; break;
        case '2': $type = 'Check-in'; break;
        case '3': $type = 'Check-out'; break;
        case '4': $type = 'DT'; break;
        case '5': $type = 'Overview'; break;
    }

    echo <<< HEREDOC
    box 15 $y 180 30
    barcode 20 $by 60 20 "$code" 1d
    fontsize 16
    text 80 $ty "$code"
    text 145 $ty "$type"
    
    HEREDOC;

    $index++;
}

echo "save \"output\"\r\n\r\n";
