<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\MamTaxiClient;
use App\Service\OrderUpdatesTracker;
use App\Service\OrderImporter;
use App\Service\OrderListTokenValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderProxyController extends AbstractController
{
    public function __construct(
        private bool $ordersFetchingDisabled,
    ) {
    }
    #[Route('/api/proxy/login', name: 'proxy_login')]
    public function login(Request $request, MamTaxiClient $client, OrderListTokenValidator $tokenValidator): JsonResponse
    {
        if ($denied = $tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        if ($client->login()) {
            return $this->json(['message' => 'Logged in']);
        }

        return $this->json(['error' => 'Login failed'], 401);
    }

    #[Route('/api/proxy/orders', name: 'proxy_orders')]
    public function getOrders(MamTaxiClient $client): JsonResponse
    {
        if ($this->ordersFetchingDisabled) {
            return $this->json(['error' => 'Order fetching is temporarily disabled.'], 503);
        }
        if (!$client->isSessionValid()) {
            return $this->json(['error' => 'Session expired'], 401);
        }

        $merged = $client->fetchOrdersWithDetails();

        return $this->json($merged);
    }

    #[Route('/api/proxy/dump-orders', name: 'proxy_dump_orders')]
    public function dump(MamTaxiClient $client): JsonResponse
    {
        if ($this->ordersFetchingDisabled) {
            return $this->json(['error' => 'Order fetching is temporarily disabled.'], 503);
        }
        $client->dumpAllOrdersToFiles();

        return $this->json(['message' => 'Zrzut zakończony']);
    }

    #[Route('/api/proxy/debug', name: 'proxy_debug')]
    public function debug(MamTaxiClient $client, SessionInterface $session): JsonResponse
    {
        return $this->json([
            'session_id' => $session->getId(),
            'cookies' => $client->getDebugCookies(),
        ]);
    }

    #[Route('/api/session/check', name: 'check_session')]
    public function checkSession(Request $request, MamTaxiClient $client, OrderListTokenValidator $tokenValidator): JsonResponse
    {
        if ($denied = $tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        if ($client->isSessionValid()) {
            return new JsonResponse('Session valid', 200);
        }

        return new JsonResponse('Session expired', 401);
    }

    /**
     * @throws \Exception
     */
    #[Route('/find-driver/{id}', name: 'find_driver')]
    public function findDriver(MamTaxiClient $client, string $id): JsonResponse
    {
        return $client->findDriver($id);
    }

    #[Route('/api/proxy/import-orders/{howMany}', name: 'admin_import_orders')]
    public function importOrdersFromExternalApi(
        Request $request,
        MamTaxiClient $client,
        OrderImporter $importer,
        OrderUpdatesTracker $updatesTracker,
        string $howMany,
        OrderListTokenValidator $tokenValidator,
    ): JsonResponse
    {
        if ($this->ordersFetchingDisabled) {
            return new JsonResponse('Order fetching is temporarily disabled.', 503);
        }
        if ($denied = $tokenValidator->denyUnlessValid($request)) {
            return $denied;
        }

        if (!$client->isSessionValid()) {
            $client->login();
        }

        $target = max(1, (int) $howMany);
        $batchSize = max(1, $request->query->getInt('batchSize', 250));
        $concurrency = max(1, $request->query->getInt('concurrency', 25));
        $imported = 0;

        for ($start = 0; $start < $target; $start += $batchSize) {
            $limit = min($batchSize, $target - $start);
            $orders = $client->fetchOrdersWithDetails($limit, $start, $concurrency);
            if ([] === $orders) {
                break;
            }

            $importer->importFromArray($orders);
            $imported += count($orders);

            if (count($orders) < $limit) {
                break;
            }
        }

        if ($imported > 0) {
            $updatesTracker->touch();
        }

        return new JsonResponse("Import complete ({$imported})");
    }

    #[Route('/api/proxy/drivers/status', name: 'proxy_drivers_status')]
    public function getDriversStatus(MamTaxiClient $client): JsonResponse
    {
        $client->refreshDriverStatuses();

        return $this->json([
            'message' => 'Statusy przetworzone',
        ]);
    }

    #[Route('/api/proxy/drivers/status/latest', name: 'proxy_drivers_status_latest')]
    public function latestDriverStatuses(MamTaxiClient $client): JsonResponse
    {
        $data = $client->driverStatuses();

        return $this->json($data);
    }

    #[Route('/fetch_statuses', name: 'statuses')]
    public function fetchDriverStatuses(MamTaxiClient $client): JsonResponse
    {
        $data = $client->fetchDriverStatuses();

        return $this->json($data);
    }
}
