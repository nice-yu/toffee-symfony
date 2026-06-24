<?php
declare(strict_types=1);

namespace App\Contract;

/**
 * 缓存 DTO 标记接口
 *
 * 实现此接口的类需继承 AbstractCacheDto，定义 $cacheKey 格式字符串。
 * RedisUtils 通过此接口统一管理缓存 Key 的读取。
 */
interface CacheDtoInterface
{
    public function getCacheKey(): string;
}
