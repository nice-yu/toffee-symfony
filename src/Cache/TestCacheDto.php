<?php
declare(strict_types=1);

namespace App\Cache;

/**
 * 缓存测试 DTO
 */
class TestCacheDto extends AbstractCacheDto
{
    protected string $cacheKey = 'test:%s';

    protected int $ttl = 60 * 10;

    public string $nickname = '';

    public int $age = 0;

    public string $role = '';
}
