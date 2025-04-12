<?php

declare(strict_types=1);


namespace App\Controller;

use App\Service\MamTaxiClient;
use App\Service\OrderImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OrderProxyController extends AbstractController
{
    #[Route('/api/proxy/login', name: 'proxy_login')]
    public function login(MamTaxiClient $client): JsonResponse
    {
        if ($client->login()) {
            return $this->json(['message' => 'Logged in']);
        }

        return $this->json(['error' => 'Login failed'], 401);
    }

    #[Route('/api/proxy/orders', name: 'proxy_orders')]
    public function getOrders(MamTaxiClient $client): JsonResponse
    {
        if (!$client->isSessionValid()) {
            return $this->json(['error' => 'Session expired'], 401);
        }

        $merged = $client->fetchOrdersWithDetails();
        return $this->json($merged);
    }

    #[Route('/api/proxy/dump-orders', name: 'proxy_dump_orders')]
    public function dump(MamTaxiClient $client): JsonResponse
    {
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
    public function checkSession(MamTaxiClient $client): JsonResponse
    {
        if ($client->isSessionValid()) {
            return new JsonResponse('Session valid', 200);
        }
        return new JsonResponse('Session expired', 401);
    }

    /**
     * @throws \Exception
     */
    #[Route('/find-driver/{id}', name: 'check_session')]
    public function findDriver(MamTaxiClient $client, string $id): JsonResponse
    {
        return $client->findDriver($id);
    }



    #[Route('/import-orders/', name: 'import_orders')]
    public function importOrders(OrderImporter $importer): JsonResponse
    {
        $importer->importFromJsonFiles(__DIR__ . '/../../var/orders');
        return new JsonResponse('Import zakończony!', 200);
    }

    #[Route('/api/proxy/import-orders/{howMany}', name: 'admin_import_orders')]
    public function importOrdersFromExternalApi(MamTaxiClient $client, OrderImporter $importer, string $howMany): JsonResponse
    {
        if (!$client->isSessionValid()) {
            $client->login();
        }

        $orders = $client->fetchOrdersWithDetails((int)$howMany);

        if (!is_array($orders)) {
            return new JsonResponse('Invalid response from MamTaxi', 400);
        }

        $importer->importFromArray($orders);

        return new JsonResponse('Import complete');
    }

    #[Route('/api/proxy/drivers/status', name: 'proxy_drivers_status')]
    public function getDriversStatus(MamTaxiClient $client): JsonResponse
    {
        if (!$client->isSessionValid()) {
            $client->login();
        }
        return $this->json($client->driverStatuses());
    }

}