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
    private const LAST_TRIGGER_FILE = 'var/price_update_last_trigger.txt';
    private const TRIGGER_LOCK_FILE = 'var/price_update_trigger.lock';
    private const DUPLICATE_WINDOW_SECONDS = 3;

    public function __construct(private LoggerInterface $logger)
    {
    }

    #[Route('/api/price-update', name: 'price_update_check', methods: ['GET'])]
    public function checkPriceUpdate(): JsonResponse
    {
        $flagPath = $this->getParameter('kernel.project_dir') . '/' . self::FLAG_FILE;

        $addPrice = false;

        // Atomowe "przejęcie" flagi: tylko jeden request wygra rename
        if (is_file($flagPath)) {
            $consumedPath = $flagPath . '.consumed.' . bin2hex(random_bytes(6));

            if (@rename($flagPath, $consumedPath)) {
                $addPrice = true;
                @unlink($consumedPath); // sprzątanie

                $this->logger->info('Price update flag consumed (atomic)', [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'action' => 'flag_consumed_atomic'
                ]);
            }
        }

        return $this->json([
            'add_price' => $addPrice,
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
        $flagPath = $projectDir . '/' . self::FLAG_FILE;
        $lastTriggerPath = $projectDir . '/' . self::LAST_TRIGGER_FILE;
        $lockPath = $projectDir . '/' . self::TRIGGER_LOCK_FILE;
        $flagDir = dirname($flagPath);

        if (!is_dir($flagDir)) {
            mkdir($flagDir, 0755, true);
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

        $now = time();
        $lastTriggerTimestamp = 0;

        if (is_file($lastTriggerPath)) {
            $lastTriggerRaw = trim((string) @file_get_contents($lastTriggerPath));
            if (ctype_digit($lastTriggerRaw)) {
                $lastTriggerTimestamp = (int) $lastTriggerRaw;
            }
        }

        $elapsed = $now - $lastTriggerTimestamp;
        if ($lastTriggerTimestamp > 0 && $elapsed >= 0 && $elapsed < self::DUPLICATE_WINDOW_SECONDS) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);

            $this->logger->info('Price update trigger ignored (duplicate window)', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'trigger_duplicate_ignored',
                'ip' => $request->getClientIp(),
                'elapsed_seconds' => $elapsed,
            ]);

            return $this->json([
                'success' => true,
                'message' => 'duplicate_ignored',
                'accepted' => false,
                'timestamp' => $now,
            ]);
        }

        @file_put_contents($lastTriggerPath, (string) $now, LOCK_EX);

        $flagCreated = false;

        // ✅ nie nadpisuj jeśli już jest ustawiona (idempotent)
        $flagHandle = @fopen($flagPath, 'x');
        if ($flagHandle !== false) {
            fwrite($flagHandle, date('Y-m-d H:i:s'));
            fclose($flagHandle);

            $flagCreated = true;

            $this->logger->info('Price update flag set', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'flag_created',
                'ip' => $request->getClientIp(),
            ]);
        } else {
            $this->logger->info('Price update flag already exists (ignored)', [
                'timestamp' => date('Y-m-d H:i:s'),
                'action' => 'flag_exists_ignored',
                'ip' => $request->getClientIp(),
            ]);
        }

        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);

        return $this->json([
            'success' => true,
            'message' => 'ok',
            'accepted' => $flagCreated,
            'timestamp' => $now,
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
