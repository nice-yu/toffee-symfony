<?php
declare(strict_types=1);

namespace App\Cache\Common;

use App\Cache\AbstractCacheDto;

/**
 * 验证码缓存 DTO - 支持图形验证码、短信验证码等
 * @uses VerifyCodeCacheDto
 */
class VerifyCodeCacheDto extends AbstractCacheDto
{
    protected string $cacheKey = 'verify:%s';

    protected int $ttl = 60 * 10;

    public string $phrase = '';

    /**
     * captcha: 图形验证码, sms: 短信验证码
     * @var string
     */
    public string $type = '';
}
