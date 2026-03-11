<?php

declare(strict_types=1);

namespace App\Service;

use App\Order\Domain\Order;
use App\Project\UseCase\AddOrder\Command;
use App\Project\UseCase\AddOrderHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class OrderImporter
{
    private const MAX_STRING_LENGTH = 255;

    public function __construct(private EntityManagerInterface $entityManager, private AddOrderHandler $addOrderHandler)
    {
    }

    public function importFromArray(array $orders): void
    {
        foreach ($orders as $data) {
            $externalId = $data['Id'] ?? null;
            if (null === $externalId) {
                continue;
            }
            $externalOrderId = $data['ExternalOrderId'] ?? null;

            $createdAt = new \DateTimeImmutable($data['CreationDate']);
            $status = $data['Status'];
            $city = $this->trimToLength((string) ($data['City'] ?? ''));
            $street = $this->trimNullable($data['Street'] ?? null);
            $house = $this->trimNullable($data['House'] ?? null);
            $from = $this->trimToLength((string) ($data['From'] ?? ''));
            $taxiNumber = $this->trimNullable($data['TaxiNumber'] ?? null);
            $destination = $this->trimNullable($data['Destination'] ?? null);
            $notes = $this->trimNullable($data['Notes'] ?? null);
            $phoneNumber = $this->trimNullable($data['PhoneNumber'] ?? null);
            $plannedArrivalDate = isset($data['PlannedArrivalDate']) ? new \DateTimeImmutable($data['PlannedArrivalDate']) : null;
            if (is_string($status)) {
                continue;
            }

            $createdAtPlusTwoHour = $createdAt?->modify('+1 hour');
            $plannedArrivalDatePlusTwoHour = $plannedArrivalDate?->modify('+1 hour');
            $companyName = $this->trimNullable($data['CompanyName'] ?? null);
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
                    paymentMethod: $paymentMethod,
                    externalOrderId: $externalOrderId
                );
            } catch (\Exception $e) {
                dd($e->getMessage().'External id: '.$externalId.'Status: '.$status);
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

    private function trimNullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);
        if ($string === '') {
            return null;
        }

        return $this->trimToLength($string);
    }

    private function trimToLength(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value) <= self::MAX_STRING_LENGTH) {
                return $value;
            }

            return (string) mb_substr($value, 0, self::MAX_STRING_LENGTH);
        }

        if (strlen($value) <= self::MAX_STRING_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::MAX_STRING_LENGTH);
    }
}
