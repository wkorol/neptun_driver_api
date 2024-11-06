<?php

declare(strict_types=1);

namespace App\DTO;

enum TariffType: int
{
    case FirstTariff = 1;
    case SecondTariff = 2;
}
