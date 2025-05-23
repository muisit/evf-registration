<?php

function createControlDigit(string $id)
{
    // create a control number by adding up all the digits
    $total = 0;
    for ($i = 0; $i < strlen($id); $i++) {
        $total += intval($id[$i]);
    }
    $control = (10 - ($total % 10)) % 10;
    return $control;
}

function createCode($func, $addfunction, $id1, $id2, $payload)
{
    $encoded = sprintf(
        "%d%03d%03d",
        (intval($addfunction) % 10),
        (intval($id1) % 1000),
        (intval($id2) % 1000)
    );
    $control = createControlDigit($encoded);

    return sprintf(
        '%d%d%s%d%04d',
        (intval($func) % 10),
        (intval($func) % 10),
        $encoded,
        ($control % 10),
        intval($payload)
    );
}


function createCardCodes($start, $max = 1000)
{
    $retval = [];
    for ($i = $start; $i < $max; $i++) {
        $id1 = random_int(101, 999);
        $addfunc = random_int(0, 9);
        $retval[] = ['code' => createCode(2, $addfunc, $id1, $i, 0), 'card' => $i];
    }
    return $retval;
}

function createDocumentCodes($start, $max = 2500, $event = 37)
{
    $retval = [];
    for ($i = $start; $i < $max; $i++) {
        $id1 = random_int(101, 999);
        $addfunc = intval($id1 / 100) % 10;
        $id1 = (10 * $id1) % 1000;
        $overflow = intval($i / 1000);
        $id1 += $overflow;
        $id2 = $i % 1000;
        $retval[] = ['code' => createCode(3, $addfunc, $id1, $id2, $event), 'document' => $i];
    }
    return $retval;
}
