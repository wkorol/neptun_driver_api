<?php

declare(strict_types=1);

namespace App\Project\UseCase;

use App\Order\Domain\Order;
use App\Order\Repository\OrderRepository;
use App\Project\UseCase\UpdateOrder\Command;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateOrderHandler
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Command $command): bool
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->findByExternalId($command->externalId);

        if (!$order) {
            throw new NotFoundHttpException(sprintf('Order with externalId %d not found.', $command->externalId));
        }


        $changed = false;
        $plannedArrivalDate = $command->plannedArrivalDate;
        if ($plannedArrivalDate) {
            $plannedArrivalDatePlus2Hours = $plannedArrivalDate->modify('+2 hour');
            if (
                $order->getPlannedArrivalDate()?->getTimestamp() !== $plannedArrivalDatePlus2Hours->getTimestamp()
            ) {
                $order->setArrivalDate($plannedArrivalDatePlus2Hours);
                $changed = true;
            }
        }

        if ($order->getStatus()?->value !== $command->status) {
            $order->setStatus($command->status);
            $changed = true;
        }

        if ($order->getNotes() !== $command->notes) {
            $order->setNotes($command->notes);
            $changed = true;
        }

        if ($order->getPhoneNumber() !== $command->phoneNumber) {
            $order->setPhoneNumber($command->phoneNumber);
            $changed = true;
        }

        if ($order->getCompanyName() !== $command->companyName) {
            $order->setCompanyName($command->companyName);
            $changed = true;
        }

        if ($order->getPrice() !== $command->price) {
            $order->setPrice($command->price);
            $changed = true;
        }

        if ($order->getPassengerCount() !== $command->passengerCount) {
            $order->setPassengerCount($command->passengerCount);
            $changed = true;
        }

        if ($order->getPaymentMethod() !== $command->paymentMethod) {
            $order->setPaymentMethod($command->paymentMethod);
            $changed = true;
        }

        if ($order->getTaxiNumber() !== $command->taxiNumber) {
            $order->setTaxiNumber($command->taxiNumber);
            $changed = true;
        }

        if ($order->getExternalOrderId() !== $command->externalOrderId) {
            $order->setExternalOrderId($command->externalOrderId);
            $changed = true;
        }

        if ($changed) {
            $this->entityManager->flush();
        }

        return $changed;
    }
}
