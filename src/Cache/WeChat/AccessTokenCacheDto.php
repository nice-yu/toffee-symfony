<?php
declare(strict_types=1);

namespace App\Cache\WeChat;

use App\Cache\AbstractCacheDto;

class AccessTokenCacheDto extends AbstractCacheDto
{
    protected string $cacheKey = 'wx:accessToken:%s';

    protected int $ttl = (60 * 60 * 2) - 100;

    public string $accessToken = '';
}
