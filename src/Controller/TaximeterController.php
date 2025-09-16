<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class TaximeterController extends AbstractController
{
    private const FLAG_FILE = 'var/price_update_flag.txt';

    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/api/price-update', name: 'price_update_check', methods: ['GET'])]
    public function checkPriceUpdate(): JsonResponse
    {
        $addPrice = false;
        $flagPath = $this->getParameter('kernel.project_dir') . '/' . self::FLAG_FILE;

        if (file_exists($flagPath)) {
            $addPrice = true;
            // Remove the flag file after reading it (one-time trigger)
            unlink($flagPath);

            $this->logger->info('Price update flag consumed', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'flag_consumed'
            ]);
        }

        return $this->json([
            'add_price' => $addPrice,
            'timestamp' => time(),
            'status' => 'success'
        ]);
    }

    #[Route('/api/price-update', name: 'price_update_trigger', methods: ['POST'])]
    public function triggerPriceUpdate(Request $request): JsonResponse
    {
        $flagPath = $this->getParameter('kernel.project_dir') . '/' . self::FLAG_FILE;
        $flagDir = dirname($flagPath);

        // Create var directory if it doesn't exist
        if (!is_dir($flagDir)) {
            mkdir($flagDir, 0755, true);
        }

        // Set flag to trigger price update
        file_put_contents($flagPath, date('Y-m-d H:i:s'));

        $this->logger->info('Price update flag set', [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'flag_created',
            'ip' => $request->getClientIp()
        ]);

        return $this->json([
            'success' => true,
            'message' => 'Price update flag set successfully',
            'timestamp' => time()
        ]);
    }

    #[Route('/api/price-update/status', name: 'price_update_status', methods: ['GET'])]
    public function getPriceUpdateStatus(): JsonResponse
    {
        $flagPath = $this->getParameter('kernel.project_dir') . '/' . self::FLAG_FILE;
        $flagExists = file_exists($flagPath);
        $flagContent = null;

        if ($flagExists) {
            $flagContent = file_get_contents($flagPath);
        }

        return $this->json([
            'flag_exists' => $flagExists,
            'flag_created_at' => $flagContent,
            'timestamp' => time()
        ]);
    }
}