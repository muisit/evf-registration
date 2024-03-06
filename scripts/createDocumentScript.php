<?php

require __DIR__ . '/libcode.php';

$start = intval($argv[1] ?? 1);
$end = intval($argv[2] ?? 999);
$event = intval($argv[3] ?? 37);
$codes = createDocumentCodes($start, $end, $event);

echo <<< HEREDOC
create A4 P
printheader no
printfooter no
creator "Muis IT"
author "Muis IT"
title "Documents"
margins 0 0 0
font helvetica
fontsize 70
border all #000000 0.5
background #ffffff

page
template doc
# size is 210 x 297
# with 10mm margins: 190 x 277
# overall box
box 10 10 190 277
# box top-left for name/country
box 10 10 190 45
# box top-right for code
box 135 10 65 45
# box bottom for code
box 10 242 190 45

# texts
fontsize 22
text 13 11 "Name/Nom/Name:"
text 13 29 "Country/Pays/Land:"
fontsize 16
text 13 55 "Indicate the number/Précisez la quantité/Geben Sie die Menge an:"
fontsize 20
text 13 65 "_____ x Bodywires/Fils de corps/Körperkabel"
text 13 75 "_____ x Mask wires/Fils de masque/Maskenkabel"
text 13 85 "_____ x Weapons/Armes/Waffen"
text 13 95 "_____ x Lame jackets/Cuirasses électrique/Elektrowesten"
text 13 105 "_____ x Masks/Masques/Masken"
text 13 120 "Other/Autre/Andere:"
image "evflogo_bw.png" 157 242 40 40
template end

HEREDOC;

$index = 0;
foreach ($codes as $codeData) {
    $code = $codeData['code'];
    $nr = sprintf("%04d", $codeData['document']);
    if ($index > 0) {
        echo "page\r\n";
    }
    $y = 0;
    if (($index % 2) == 1) {
        $y += 135;
    }
    $y2 = $y + 60;

    echo <<< HEREDOC
    template print doc 0 0
    barcode 148 15 40 25 "$code" 1d
    barcode 22 247 40 25 "$code" 1d
    fontsize 20
    text 138 42 "$code"   
    text 13 275 "$code"

    HEREDOC;

    $index++;
}

echo "save \"output\"\r\n\r\n";
