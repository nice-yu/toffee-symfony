<?php
declare(strict_types=1);

namespace App\Cache\Common;

use App\Cache\AbstractCacheDto;

class AttemptCacheDto extends AbstractCacheDto
{
    protected string $cacheKey = 'verify:attempts:%s';

    protected int $ttl = 60 * 10;

    public int $limit = 0;
}