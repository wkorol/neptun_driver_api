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
    private const QUEUE_FILE = 'var/price_update_queue.txt';
    private const QUEUE_LOCK_FILE = 'var/price_update_queue.lock';

    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/api/price-update', name: 'price_update_check', methods: ['GET'])]
    public function checkPriceUpdate(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $queuePath = $projectDir . '/' . self::QUEUE_FILE;
        $lockPath = $projectDir . '/' . self::QUEUE_LOCK_FILE;
        $addPrice = false;
        $queueCount = 0;

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle === false || !flock($lockHandle, LOCK_EX)) {
            if ($lockHandle !== false) {
                fclose($lockHandle);
            }

            $this->logger->error('Queue lock failed during check', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'queue_lock_check_failed',
            ]);

            return $this->json([
                'add_price' => false,
                'queue_count' => 0,
                'timestamp' => time(),
                'status' => 'error',
            ], 500)
                ->setPrivate()
                ->setMaxAge(0)
                ->setSharedMaxAge(0);
        }

        if (is_file($queuePath)) {
            $rawQueueCount = trim((string) @file_get_contents($queuePath));
            if (ctype_digit($rawQueueCount)) {
                $queueCount = (int) $rawQueueCount;
            }
        }

        if ($queueCount > 0) {
            $addPrice = true;
            $queueCount--;

            @file_put_contents($queuePath, (string) $queueCount, LOCK_EX);

            $this->logger->info('Price update consumed from queue', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'queue_item_consumed',
                'remaining_queue' => $queueCount,
            ]);
        }

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        return $this->json([
            'add_price' => $addPrice,
            'queue_count' => $queueCount,
            'timestamp' => time(),
            'status' => 'success'
        ])
            // bonus: niech proxy/klient nie cache’uje
            ->setPrivate()
            ->setMaxAge(0)
            ->setSharedMaxAge(0);
    }

    #[Route('/api/price-update', name: 'price_update_trigger', methods: ['POST'])]
    public function triggerPriceUpdate(Request $request): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $queuePath = $projectDir . '/' . self::QUEUE_FILE;
        $lockPath = $projectDir . '/' . self::QUEUE_LOCK_FILE;
        $queueDir = dirname($queuePath);

        if (!is_dir($queueDir)) {
            mkdir($queueDir, 0755, true);
        }

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle === false) {
            $this->logger->error('Price update lock file cannot be opened', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'lock_open_failed',
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'success' => false,
                'message' => 'lock_error',
                'timestamp' => time(),
            ], 500);
        }

        if (!flock($lockHandle, LOCK_EX)) {
            fclose($lockHandle);

            $this->logger->error('Price update lock could not be acquired', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'lock_acquire_failed',
                'ip' => $request->getClientIp(),
            ]);

            return $this->json([
                'success' => false,
                'message' => 'lock_error',
                'timestamp' => time(),
            ], 500);
        }

        $queueCount = 0;
        if (is_file($queuePath)) {
            $rawQueueCount = trim((string) @file_get_contents($queuePath));
            if (ctype_digit($rawQueueCount)) {
                $queueCount = (int) $rawQueueCount;
            }
        }

        $queueCount++;
        @file_put_contents($queuePath, (string) $queueCount, LOCK_EX);

        $this->logger->info('Price update queued', [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'queue_item_added',
            'ip' => $request->getClientIp(),
            'queue_count' => $queueCount,
        ]);

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        return $this->json([
            'success' => true,
            'message' => 'ok',
            'accepted' => true,
            'queue_count' => $queueCount,
            'timestamp' => time(),
        ]);
    }

    #[Route('/api/price-update/status', name: 'price_update_status', methods: ['GET'])]
    public function getPriceUpdateStatus(): JsonResponse
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $queuePath = $projectDir . '/' . self::QUEUE_FILE;
        $lockPath = $projectDir . '/' . self::QUEUE_LOCK_FILE;
        $queueCount = 0;

        $lockHandle = @fopen($lockPath, 'c+');
        if ($lockHandle !== false && flock($lockHandle, LOCK_SH)) {
            if (is_file($queuePath)) {
                $rawQueueCount = trim((string) @file_get_contents($queuePath));
                if (ctype_digit($rawQueueCount)) {
                    $queueCount = (int) $rawQueueCount;
                }
            }

            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        } elseif ($lockHandle !== false) {
            fclose($lockHandle);
        }

        return $this->json([
            'flag_exists' => $queueCount > 0, // compatibility
            'flag_created_at' => null, // compatibility
            'queue_count' => $queueCount,
            'has_pending_updates' => $queueCount > 0,
            'timestamp' => time()
        ]);
    }
}
