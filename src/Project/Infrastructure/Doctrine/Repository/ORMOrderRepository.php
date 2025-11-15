<?php

declare(strict_types=1);

namespace App\Project\Infrastructure\Doctrine\Repository;

use App\Order\Domain\Order;
use App\Order\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ORMOrderRepository implements OrderRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return Order[]|null
     */
    public function findActualOrders(): ?array
    {
        return $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(25)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Order[]|null
     */
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

    /**
     * @return Order[]|null
     */
    public function findScheduledOrdersForNext5Days(): ?array
    {
        $start = new \DateTimeImmutable('tomorrow 00:00:00');
        $end = (new \DateTimeImmutable('today'))->modify('+5 days')->setTime(23, 59, 59);

        return $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
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

    public function deleteAllFinished(): void
    {
        $qb = $this->$this->entityManager->getRepository(Order::class)->createQueryBuilder('o');

        $qb->delete()
            ->where($qb->expr()->in('o.status', [8, 12, 7]));

        $qb->getQuery()->execute();
    }

    public function findByExternalId(int $externalId): ?Order
    {
        return $this->entityManager->getRepository(Order::class)->findOneBy(['externalId' => $externalId]);
    }

    public function save(Order $order): void
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush();
    }

    /**
     * @return Order[]
     */
    public function all(): array
    {
        return $this->entityManager->getRepository(Order::class)->findAll();
    }

    public function findLast3OrdersWithPhoneNumber(string $phoneNumber, int $externalId): ?array
    {
        return $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.phoneNumber = :phoneNumber')
            ->andWhere('o.phoneNumber != :excluded')
            ->andWhere('o.externalId != :externalId')
            ->setParameter('excluded', '0048585111555')
            ->setParameter('phoneNumber', $phoneNumber)
            ->setParameter('externalId', $externalId)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
