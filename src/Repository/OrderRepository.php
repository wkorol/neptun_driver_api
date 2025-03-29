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
            if ($existing->getStatus()?->value !== $order->getStatus()?->value) {
                $this->updateOrderStatus($existing, $order->getStatus()?->value);
            }
            if ($existing?->getPlannedArrivalDate() !== $order?->getPlannedArrivalDate()) {
                $this->updatePlannedArrivalDate($existing, $order->getPlannedArrivalDate());
            }
            return;
        }

        $this->getEntityManager()->persist($order);
    }

    public function checkIfExists(int $externalId, int $status): void
    {
        /**
         * @var Order|null $order
         */
        $order = $this->findOneBy(['externalId' => $externalId]);

        if ($order) {
            if ($order->getStatus()->value !== $status) {
                $this->updateOrderStatus($order, $status);
            } else {
                throw new \PDOException('Zamówienie istnieje już w systemie.');
            }
        }
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

        return $this->createQueryBuilder('o')
            ->where('o.plannedArrivalDate IS NOT NULL')
            ->andWhere('o.status = :status')
            ->andWhere('o.plannedArrivalDate BETWEEN :start AND :end')
            ->setParameter('status', 4)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
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

}
