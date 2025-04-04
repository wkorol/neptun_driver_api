<?php

namespace App\Repository;

use App\Entity\Hotel;
use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hotel>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
    public function addOrder(Order $order): void
    {
        /**
         * @var Order|null $existing
         */
        $existing = $this->findOneBy(['externalId' => $order->getExternalId()]);

        if ($existing) {
            $this->updateOrder($existing, $order->jsonSerialize());
            return;
        }

        $this->getEntityManager()->persist($order);
    }

    public function updateOrderStatus(Order $order, ?int $status): void
    {
        $order->setStatus($status);
    }

    public function updatePlannedArrivalDate(Order $order, \DateTimeImmutable $plannedArrivalDate): void
    {
        $order->setArrivalDate($plannedArrivalDate);
    }

    public function findActualOrders(): ?array
    {
        return $this->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    public function findScheduledOrdersForToday(): ?array
    {
        $startOfDay = new \DateTimeImmutable('today 00:00:00');
        $endOfDay = new \DateTimeImmutable('today 23:59:59');
        $oneHourAgo = (new \DateTimeImmutable())->modify('-1 hour');

        return $this->createQueryBuilder('o')
            ->where('o.plannedArrivalDate IS NOT NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.plannedArrivalDate BETWEEN :start AND :end')
            ->andWhere('o.plannedArrivalDate > :oneHourAgo')
            ->setParameter('status', 4)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->setParameter('oneHourAgo', $oneHourAgo)
            ->orderBy('o.plannedArrivalDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findScheduledOrdersForNext5Days(): ?array
    {
        $start = new \DateTimeImmutable('tomorrow 00:00:00');
        $end = (new \DateTimeImmutable('today'))->modify('+5 days')->setTime(23, 59, 59);

        return $this->createQueryBuilder('o')
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

    private function updateCreatedAt(?Order $order, \DateTimeImmutable $createdAt): void
    {
        $order->setCreatedAt($createdAt);
    }

    public function deleteAllFinished(): void
    {
        $qb = $this->createQueryBuilder('o');

        $qb->delete()
            ->where($qb->expr()->in('o.status', [8, 12, 7]));

        $qb->getQuery()->execute();
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
        } catch (\Exception $e) {
            dd($e->getMessage());
        }


        return $changed;
    }
}
