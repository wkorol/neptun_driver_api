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
        $existing = $this->findOneBy(['externalId' => $order->getExternalId()]);

        if ($existing) {
            if ($existing->getStatus()?->value !== $order->getStatus()?->value) {
                $this->updateOrderStatus($existing, $order->getStatus()?->value);
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

    public function findScheduledOrders(): ?array
    {
        return $this->createQueryBuilder('o')
            ->where('o.plannedArrivalDate IS NOT NULL')
            ->andWhere('o.status = 4')
            ->orderBy('o.plannedArrivalDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
