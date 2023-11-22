<?php

namespace App\Support\Enums;

enum PaymentOptions: string
{
    case Group = 'G';
    case Individual = 'I';
    case Organisation = 'O';
    case EVF = 'E';
}
