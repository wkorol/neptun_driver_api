<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Order\Domain\Order;
use App\Order\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class ORMOrderRepository implements OrderRepository
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function addOrder(Order $order): void
    {
        /**
         * @var Order|null $existing
         */
        $existing = $this->entityManager->getRepository(Order::class)->findOneBy(['externalId' => $order->getExternalId()]);

        if ($existing) {
            $this->updateOrder($existing, $order->jsonSerialize());
            return;
        }

        $this->entityManager->persist($order);
    }

    public function findActualOrders(): ?array
    {
        return $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function findScheduledOrdersForToday(): ?array
    {
        $startOfDay = new \DateTimeImmutable('today 00:00:00');
        $endOfDay = new \DateTimeImmutable('today 23:59:59');
        $oneMinuteAgo = (new \DateTimeImmutable())->modify('-1 minutes');

        return $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.plannedArrivalDate IS NOT NULL')
            ->andWhere('o.taxiNumber IS NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.plannedArrivalDate BETWEEN :start AND :end')
            ->andWhere('o.plannedArrivalDate > :oneMinuteAgo')
            ->setParameter('status', 4)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('oneMinuteAgo', $oneMinuteAgo)
            ->orderBy('o.plannedArrivalDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findScheduledOrdersForNext5Days(): ?array
    {
        $start = new \DateTimeImmutable('tomorrow 00:00:00');
        $end = (new \DateTimeImmutable('today'))->modify('+5 days')->setTime(23, 59, 59);

        return $this->$this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.plannedArrivalDate IS NOT NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.plannedArrivalDate BETWEEN :start AND :end')
            ->setParameter('status', 4)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('o.plannedArrivalDate', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function updateOrder(Order $order, array $data): bool
    {
        $changed = false;

        $plannedArrivalDate = isset($data['PlannedArrivalDate']) ? new \DateTimeImmutable($data['PlannedArrivalDate']) : null;

        $status = $data['Status'];
        if (is_string($status)) {
            return false;
        }

        $notes = $data['Notes'] ?? null;
        $phoneNumber = $data['PhoneNumber'] ?? null;
        $companyName = $data['CompanyName'] ?? null;
        $price = $data['Price'] ?? null;
        $passengerCount = $data['PassengersCount'] ?? null;
        $paymentMethod = $data['PaymentMethod'] ?? null;

        $plannedArrivalDatePlusTwoHours = $plannedArrivalDate?->modify('+2 hours');

        try {
            if (
                $order->getPlannedArrivalDate()?->getTimestamp() !== $plannedArrivalDatePlusTwoHours?->getTimestamp()
            ) {
                $order->setArrivalDate($plannedArrivalDatePlusTwoHours);
                $changed = true;
            }

            if ($order->getStatus()?->value) {
                if ($order->getStatus()->value !== $status) {
                    $order->setStatus($status);
                    $changed = true;
                }
            }

            if ($order->getNotes() !== $notes) {
                $order->setNotes($notes);
                $changed = true;
            }

            if ($order->getPhoneNumber() !== $phoneNumber) {
                $order->setPhoneNumber($phoneNumber);
                $changed = true;
            }

            if ($order->getCompanyName() !== $companyName) {
                $order->setCompanyName($companyName);
                $changed = true;
            }

            if ($order->getPrice() !== $price) {
                $order->setPrice($price);
                $changed = true;
            }

            if ($order->getPassengerCount() !== $passengerCount) {
                $order->setPassengerCount($passengerCount);
                $changed = true;
            }

            if ($order->getPaymentMethod() !== $paymentMethod) {
                $order->setPaymentMethod($paymentMethod);
                $changed = true;
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }


        return $changed;
    }

    public function deleteAllFinished(): void
    {
        $qb = $this->$this->entityManager->getRepository(Order::class)->createQueryBuilder('o');

        $qb->delete()
            ->where($qb->expr()->in('o.status', [8, 12, 7]));

        $qb->getQuery()->execute();
    }
}