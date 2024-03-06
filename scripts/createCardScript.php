<?php

require __DIR__ . '/libcode.php';

$start = intval($argv[1] ?? 1);
$end = intval($argv[2] ?? 999);
$codes = createCardCodes($start, $end);

echo <<< HEREDOC
create A4 P
printheader no
printfooter no
creator "Muis IT"
author "Muis IT"
title "Cards"
margins 0 0 0
font helvetica
fontsize 70
border all #000000 0.5
background #ffffff
page
template img 30 30
image "evflogo_bw.png" 0 0 24 24
template end

HEREDOC;

$index = 0;
foreach ($codes as $codeData) {
    $code = $codeData['code'];
    $nr = sprintf("%03d", $codeData['card']);
    if (($index % 2) == 0 && $index > 0) {
        echo "page\r\n";
    }
    $y = 0;
    if (($index % 2) == 1) {
        $y += 135;
    }
    $y2 = $y + 60;
    $imgy = $y + 50;
    $imgy2 = $y2 + 50;

    echo <<< HEREDOC
    template box$index 200 80
    box 15 15 180 60
    barcode 20 25 100 40 "$code" 1d
    fontsize 70
    text 80 23 "Card: $nr"
    fontsize 20
    text 105 53 "$code"
    template end

    template print box$index 0 $y
    template print box$index 0 $y2
    template print img 168 $imgy
    template print img 168 $imgy2
    
    HEREDOC;

    $index++;
}

echo "save \"output\"\r\n\r\n";
