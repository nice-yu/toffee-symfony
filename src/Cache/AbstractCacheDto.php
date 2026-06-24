<?php
declare(strict_types=1);

namespace App\Cache;

use App\Contract\CacheDtoInterface;

abstract class AbstractCacheDto implements CacheDtoInterface
{
    protected string $cacheKey = '';

    protected int $ttl = 0;

    public function __construct(...$args)
    {
        if (!empty($args)) {
            $this->cacheKey = sprintf($this->cacheKey, ...$args);
        }
    }

    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }
}
