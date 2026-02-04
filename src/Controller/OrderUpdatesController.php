<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\OrderUpdatesTracker;
use App\Service\OrderListTokenValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class OrderUpdatesController extends AbstractController
{
    public function __construct(
        private OrderUpdatesTracker $tracker,
        private OrderListTokenValidator $tokenValidator,
    )
    {
    }

    #[Route('/api/orders/stream', name: 'orders_stream', methods: ['GET'])]
    public function streamOrders(Request $request): StreamedResponse
    {
        if ($denied = $this->tokenValidator->denyUnlessValid($request)) {
            return new StreamedResponse(function () use ($denied): void {
                $payload = $denied->getContent();
                echo $payload !== false ? $payload : '{"error":"Invalid token"}';
            }, $denied->getStatusCode(), $denied->headers->all());
        }

        $lastEventId = $request->headers->get('Last-Event-ID');
        $since = $request->query->getInt('since', 0);
        $lastSeen = $lastEventId !== null ? (int) $lastEventId : $since;
        $tracker = $this->tracker;

        $response = new StreamedResponse(function () use ($tracker, $lastSeen): void {
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', '0');

            $currentSeen = $lastSeen;
            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $currentCounter = $tracker->getCounter();
                if ($currentCounter > $currentSeen) {
                    $payload = [
                        'id' => $currentCounter,
                        'timestamp' => $tracker->getTimestamp(),
                        'type' => 'orders_updated',
                    ];

                    echo "id: {$currentCounter}\n";
                    echo "event: orders_updated\n";
                    echo 'data: '.json_encode($payload)."\n\n";
                    $currentSeen = $currentCounter;
                } else {
                    echo ": ping\n\n";
                }

                @ob_flush();
                @flush();
                usleep(1500000);
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
}
