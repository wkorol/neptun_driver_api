<?php

declare(strict_types=1);

namespace App\Project\UseCase\AddOrder;

use App\Order\Domain\Order;

class Command
{
    public function __construct(
        public readonly Order $order,
    ) {
    }
}
