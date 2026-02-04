<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

class OrderUpdatesTracker
{
    private const COUNTER_KEY = 'orders_updates_counter';
    private const TIMESTAMP_KEY = 'orders_updates_timestamp';

    public function __construct(private CacheItemPoolInterface $cache)
    {
    }

    public function touch(): int
    {
        $counterItem = $this->cache->getItem(self::COUNTER_KEY);
        $counter = $counterItem->isHit() ? (int) $counterItem->get() : 0;
        ++$counter;
        $counterItem->set($counter);
        $this->cache->save($counterItem);

        $timestampItem = $this->cache->getItem(self::TIMESTAMP_KEY);
        $timestampItem->set(time());
        $this->cache->save($timestampItem);

        return $counter;
    }

    public function getCounter(): int
    {
        $counterItem = $this->cache->getItem(self::COUNTER_KEY);

        return $counterItem->isHit() ? (int) $counterItem->get() : 0;
    }

    public function getTimestamp(): ?int
    {
        $timestampItem = $this->cache->getItem(self::TIMESTAMP_KEY);

        return $timestampItem->isHit() ? (int) $timestampItem->get() : null;
    }
}
