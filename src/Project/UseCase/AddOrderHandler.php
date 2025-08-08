<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Order\Repository\OrderRepository;
use App\Project\UseCase\AddOrder\Command;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class AddOrderHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private UpdateOrderHandler $updateOrderHandler
    ) {
    }

    public function __invoke(Command $command): void
    {
        if ($this->orderRepository->findByExternalId($command->order->getExternalId())) {
            $this->updateOrderHandler->__invoke(new UpdateOrder\Command(
                $command->order->getExternalId(),
                $command->order->getPlannedArrivalDate(),
                $command->order->getStatus()->value,
                $command->order->getNotes(),
                $command->order->getPhoneNumber(),
                $command->order->getCompanyName(),
                $command->order->getPrice(),
                $command->order->getPassengerCount(),
                $command->order->getPaymentMethod()
            ));
            return;
        }

        $this->orderRepository->save($command->order);
    }
}