<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Order\Repository\OrderRepository;
use App\Project\UseCase\DeleteAllFinishedOrders\Command;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteAllFinishedOrdersHandler
{
    public function __construct(private OrderRepository $orderRepository)
    {
    }

    public function __invoke(Command $command): void
    {
        $this->orderRepository->deleteAllFinished();
    }
}
