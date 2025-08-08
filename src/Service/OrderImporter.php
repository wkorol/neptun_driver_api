<?php

declare(strict_types=1);


namespace App\Service;

use App\Order\Domain\Order;
use App\Order\Repository\OrderRepository;
use App\Project\UseCase\AddOrder\Command;
use App\Project\UseCase\AddOrderHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;


class OrderImporter
{
    public function __construct(private EntityManagerInterface $entityManager, private AddOrderHandler $addOrderHandler) {

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

            $createdAtPlusTwoHour = $createdAt?->modify('+2 hour');
            $plannedArrivalDatePlusTwoHour = $plannedArrivalDate?->modify('+2 hour');
            $companyName = $data['CompanyName'] ?? null;
            $price = $data['Price'] ?? null;
            $passengerCount = $data['PassengersCount'] ?? null;
            $paymentMethod = $data['PaymentMethod'] ?? null;

            try {
                $order = new Order(
                    id: Uuid::v4(),
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
                    passengerCount: $passengerCount,
                    paymentMethod: $paymentMethod
                );
            } catch (\Exception $e) {
                dd($e->getMessage() . 'External id: ' . $externalId . 'Status: '. $status);
            }


            try {
                $this->addOrderHandler->__invoke(new Command(
                    $order
                ));
            } catch (\PDOException $exception) {
                continue;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}