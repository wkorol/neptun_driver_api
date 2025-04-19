<?php

namespace App\MessageHandler;

use App\Message\GetDriverStatuses;
use App\Service\MamTaxiClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetDriverStatusesHandler
{
    public function __construct(private MamTaxiClient $client)
    {
    }

    public function __invoke(GetDriverStatuses $message): void
    {
        $this->client->refreshDriverStatuses();
    }
}
