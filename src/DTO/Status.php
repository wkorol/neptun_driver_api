<?php

declare(strict_types=1);

namespace App\DTO;

enum Status: int
{
    case Registered = 4;
    case WaitingForTaxi = 5;
    case WaitingForClient = 6;
    case CancelledByUser = 7;
    case Cancelled = 8;
    case NoPassenger = 9;
    case InProgress = 11;
    case Finished = 12;

    public function toLabel(): string
    {
        return match ($this) {
            self::WaitingForClient => 'Oczekuje na klienta',
            self::WaitingForTaxi => 'Taksówka w drodze po klienta',
            self::Registered => 'Kurs zarejestrowany w korporacji',
            self::Cancelled => 'Kurs anulowany przez korporacje',
            self::NoPassenger => 'Kierowca stwierdził brak pasażera',
            self::CancelledByUser => 'Kurs anulowany przez klienta',
            self::InProgress => 'Kierowca jest w trakcie kursu z klientem',
            self::Finished => 'Kurs zakończony'
        };
    }
}
