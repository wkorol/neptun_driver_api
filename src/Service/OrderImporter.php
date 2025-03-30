<?php

declare(strict_types=1);


namespace App\Service;

use App\DTO\Status;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;


class OrderImporter
{
    public function __construct(private EntityManagerInterface $em, private OrderRepository $orderRepository) {

    }

    public function importFromArray(array $orders): void
    {
        foreach ($orders as $data) {
            $externalId = $data['Id'] ?? null;
            if ($externalId === null) {
                continue;
            }

            $createdAt = new \DateTimeImmutable($data['CreationDate']);
            $status = $data['Status'];
            $city = $data['City'];
            $street = $data['Street'] ?? null;
            $house = $data['House'] ?? null;
            $from = $data['From'] ?? null;
            $taxiNumber = $data['TaxiNumber'] ?? null;
            $destination = $data['Destination'] ?? null;
            $notes = $data['Notes'] ?? null;
            $phoneNumber = $data['PhoneNumber'] ?? null;
            $plannedArrivalDate = isset($data['PlannedArrivalDate']) ? new \DateTimeImmutable($data['PlannedArrivalDate']) : null;
            if (is_string($status)) {
                continue;
            }

            // Add 1 hour
            $createdAtPlusTwoHour = $createdAt?->modify('+2 hour');
            $plannedArrivalDatePlusTwoHour = $plannedArrivalDate?->modify('+2 hour');
            $companyName = $data['CompanyName'] ?? null;
            $price = $data['Price'] ?? null;
            $passengerCount = $data['PassengersCount'] ?? null;

            try {
                $order = new Order(
                    externalId: $externalId,
                    createdAt: $createdAtPlusTwoHour,
                    plannedArrivalDate: $plannedArrivalDatePlusTwoHour,
                    status: $status,
                    city: $city,
                    street: $street,
                    house: $house,
                    from: $from,
                    taxiNumber: $taxiNumber,
                    destination: $destination,
                    notes: $notes,
                    phoneNumber: $phoneNumber,
                    companyName: $companyName,
                    price: $price,
                    passengerCount: $passengerCount
                );
            } catch (\Exception $e) {
                dd($e->getMessage() . 'External id: ' . $externalId . 'Status: '. $status);
            }


            try {
                $this->orderRepository->addOrder($order);
            } catch (\PDOException $exception) {
                continue;
            }
        }

        $this->em->flush();
        $this->em->clear();
    }

    public function importFromJsonFiles(string $dir): void
    {
        $files = glob($dir . '/orders_*.json');
        usort($files, function ($a, $b) {
            preg_match('/orders_(\d+)\.json$/', $a, $matchA);
            preg_match('/orders_(\d+)\.json$/', $b, $matchB);
            return ((int) $matchA[1]) <=> ((int) $matchB[1]);
        });

        foreach ($files as $filePath) {
            $json = file_get_contents($filePath);
            $orders = json_decode($json, true);

            if (!is_array($orders)) {
                continue;
            }

            foreach ($orders as $data) {
                $externalId = $data['Id'];
                $createdAt = new \DateTimeImmutable($data['CreationDate']);
                $status = $data['Status'];
                $city = $data['City'];
                $street = $data['Street'] ?? null;
                $house = $data['House'] ?? null;
                $from = $data['From'] ?? null;
                $taxiNumber = $data['TaxiNumber'] ?? null;
                $destination = $data['Destination'] ?? null;
                $notes = $data['Notes'] ?? null;
                $phoneNumber = $data['PhoneNumber'] ?? null;
                $plannedArrivalDate = $data['PlannedArrivalDate'] ? new \DateTimeImmutable($data['PlannedArrivalDate']) : null;

                // Add 1 hour
                $createdAtPlusOneHour = $createdAt?->modify('+2 hour');
                $plannedArrivalDatePlusOneHour = $plannedArrivalDate?->modify('+2 hour');
                $companyName = $data['CompanyName'] ?? null;
                $price = $data['Price'] ?? null;
                $passengerCount = $data['PassengerCount'] ?? null;

                if ($externalId === null) {
                    continue;
                }

                $order = new Order(
                    externalId: $externalId,
                    createdAt: $createdAtPlusOneHour,
                    plannedArrivalDate: $plannedArrivalDatePlusOneHour,
                    status: $status,
                    city: $city,
                    street: $street,
                    house: $house,
                    from: $from,
                    taxiNumber: $taxiNumber,
                    destination: $destination,
                    notes: $notes,
                    phoneNumber: $phoneNumber,
                    companyName: $companyName,
                    price: $price,
                    passengerCount: $passengerCount
                );

                if ($order->getStatus() !== Status::Registered) {
                    continue;
                }

                try {
                    $this->orderRepository->addOrder($order);
                } catch (\PDOException $exception) {
                    continue;
                }

            }

            $this->em->flush();
            $this->em->clear();
        }
    }
}