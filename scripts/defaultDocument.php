<?php

function documentParameters()
{
    $pretext = <<< HEREDOC
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
font helveticaI
text 13 55 "Indicate the number/Précisez la quantité/Geben Sie die Menge an:"
font helvetica
text 13 70  "_____ (max: 4) Body wires/Fils de corps/Körperkabel"
text 13 83  "_____ (max: 4) Mask wires/Fils de masque/Maskenkabel"
text 13 96  "_____ (max: 4) Weapons/Armes/Waffen"
text 13 109  "_____ (max: 2) Lame jackets/Cuirasses électrique/Elektrowesten"
text 13 122 "_____ (max: 2) Masks/Masques/Masken"
text 13 135 "_____ (max: 2) Jackets/Vestes/Jacken"
text 13 148 "_____ (max: 2) Breeches/Pantalons/Hosen"
text 13 161 "_____ (max: 2) Plastrons/Cuirasses de protection/Unterziehjacken"
text 13 174 "_____ (max: 2) Gloves/Gants/Handschuhe"
image "evflogo_bw.png" 157 242 40 40
template end


HEREDOC;

    return [
        'template' => $pretext,
        'code1' => [148, 15, 40, 25],
        'code2' => [22, 247, 40, 25],
        'fontsize' => 20,
        'text1' => [138, 42],
        'text2' => [13, 275]
    ];
}
