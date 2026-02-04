<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

class OrderListLinkService
{
    private string $storagePath;

    public function __construct(#[Autowire('%kernel.project_dir%')] string $projectDir)
    {
        $this->storagePath = rtrim($projectDir, '/').'/var/order-list-link.json';
    }

    public function getToken(): string
    {
        if (!is_file($this->storagePath)) {
            return $this->resetToken();
        }

        $contents = file_get_contents($this->storagePath);
        if ($contents === false) {
            return $this->resetToken();
        }

        $data = json_decode($contents, true);
        if (!is_array($data) || !isset($data['token']) || !is_string($data['token']) || $data['token'] === '') {
            return $this->resetToken();
        }

        return $data['token'];
    }

    public function hasToken(): bool
    {
        if (!is_file($this->storagePath)) {
            return false;
        }

        $contents = file_get_contents($this->storagePath);
        if ($contents === false) {
            return false;
        }

        $data = json_decode($contents, true);

        return is_array($data) && isset($data['token']) && is_string($data['token']) && $data['token'] !== '';
    }

    public function resetToken(): string
    {
        $token = Uuid::v4()->toRfc4122();
        $payload = [
            'token' => $token,
            'updatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $encoded = json_encode($payload, JSON_PRETTY_PRINT);
        if ($encoded === false) {
            return $token;
        }

        $directory = dirname($this->storagePath);
        if (!is_dir($directory)) {
            @mkdir($directory, 0775, true);
        }

        file_put_contents($this->storagePath, $encoded);

        return $token;
    }
}
