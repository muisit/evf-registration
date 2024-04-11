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
# 3 boxes per row
box 10 20 60 10
box 70 20 60 10
box 130 20 70 10

box 10 30 60 10
box 70 30 60 10
box 130 30 70 10

box 10 40 60 10
box 70 40 60 10
box 130 40 70 10

box 10 50 60 10
box 70 50 60 10
box 130 50 70 10

box 10 60 60 10
box 70 60 60 10
box 130 60 70 10

# second row of boxes
background #dddddd
box 10 75 60 10
box 70 75 30 10
box 100 75 30 10
box 130 75 70 10

background #ffffff
box 10 85 35 10
box 45 85 25 10
box 70 85 30 10
box 100 85 30 10
box 130 85 70 10

box 10 95 60 10
box 70 95 30 10
box 100 95 30 10
box 130 95 70 10

box 10 105 60 10
box 70 105 30 10
box 100 105 30 10
box 130 105 70 10

box 10 115 60 10
box 70 115 30 10
box 100 115 30 10
box 130 115 70 10

box 10 125 60 10
box 70 125 30 10
box 100 125 30 10
box 130 125 70 10

box 10 135 60 10
box 70 135 30 10
box 100 135 30 10
box 130 135 70 10

box 10 145 60 10
box 70 145 30 10
box 100 145 30 10
box 130 145 70 10

box 10 155 60 10
box 70 155 30 10
box 100 155 30 10
box 130 155 70 10

box 10 165 60 10
box 70 165 30 10
box 100 165 30 10
box 130 165 70 10

box 10 175 60 10
box 70 175 30 10
box 100 175 30 10
box 130 175 70 10

box 10 185 60 10
box 70 185 30 10
box 100 185 30 10
box 130 185 70 10

# check in row
box 10 200 60 30
box 70 200 60 10
box 130 200 70 10
box 70 210 60 20
box 130 210 70 20

# check out row
box 10 235 60 30
box 70 235 60 10
box 130 235 70 10
box 70 245 60 20
box 130 245 70 20

# texts
fontsize 16
text 40 8 "MATERIAL CONTROL / CONTROLE DU MATERIEL"
fontsize 9
text 82 20.5 "NAME, FIRST NAME /"
text 86 25 "NOM, PRENOM"
text 84 32.5 "WEAPON / ARME*"
text 142 32.5 "EPEE           FOIL           SABEL"
text 84.5 42.5 "GENDER / SEXE*"
text 144 42.5 "MAN                        WOMAN"
text 24 52.5 "NUMBER / NUMERO"
text 80 52.5 "CATEGORY / CATEGORIE*"
text 141 52.5 "VETERAN            GRAND VETERAN"
text 84 62.5 "COUNTRY / PAYS"
fontsize 6
text 70 71 "* CIRCLE YOUR CHOICE / ENTOURER VOTRE CHOIX"

fontsize 9
text 30 77.9 "DESCRIPTION"
text 81 77.9 "QTY"
text 111 77.9 "OK"
text 158 77.9 "REMARKS"
text 12 88.5 "WEAPON / ARME*"
text 50 88.5 "E    F    S"
text 27 98.5 "MASK / MASQUE"
text 27.2 108.5 "JACKET / VESTE"
text 25 118.5 "PANTS / PANTALON"
text 13 128.5 "UNDERPLASTRON / SOUS-VESTE"
text 24 135.5 "ELECTRIC JACKET/"
text 24 140 "VESTE ELECTRIQUE"
text 22 145.5 "BREAST PROTECTION /"
text 22 150 "PROTECTION POITRINE"
text 28 158.5 "GLOVE / GANT"
text 17 168.5 "BODY WIRE / FIL DE CORPS"
text 16 178.5 "MASK WIRE / FIL DE MASQUE"
text 12 188.5 "MASK BIB / BAVETTE DE MASQUE"

text 34 202.5 "CHECK IN"
text 96 202.5 "DATE, TIME"
text 141 202.5 "SIGNATURE CONTROL TEAM"

text 34 237.5 "CHECK OUT"
text 96 237.5 "DATE, TIME"
text 141 237.5 "SIGNATURE FENCER"

image "evflogo_bw.png" 181 3.5 15 15
image "ciney.png" 10 20 60 30
template end


HEREDOC;

    return [
        'template' => $pretext,
        'code1' => [15, 207.5, 50, 20],
        'code2' => [15, 242.5, 50, 20],
        'fontsize' => 16,
        'text1' => [15, 61.5],
    ];
}
