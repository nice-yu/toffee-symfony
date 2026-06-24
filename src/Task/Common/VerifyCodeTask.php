<?php
declare(strict_types=1);

namespace App\Task\Common;

use App\Cache\Common\AttemptCacheDto;
use App\Cache\Common\VerifyCodeCacheDto;
use App\Exceptions\ValidatorParamsException;
use App\Utils\RedisUtils;
use Psr\Log\LoggerInterface;

/**
 * 验证码校验 Task - 支持图形验证码、短信验证码等
 * 从 Redis 取出答案 → 根据类型验证 → 删除记录（一次性消费，防重放）
 * @uses VerifyCodeTask
 */
class VerifyCodeTask
{
    public function __construct(
        private RedisUtils      $redis,
        private LoggerInterface $logger,
    ) {}

    /**
     * 验证图形验证码
     * @param string $verifyKey     验证码唯一标识
     * @param string $code          用户输入的验证码
     * @throws ValidatorParamsException 当验证失败时抛出
     */
    public function run(string $verifyKey, string $code): void
    {
        if (($_SERVER['APP_ENV_CONFIG'] ?? '') === 'local') {
            return;
        }

        /** 号段跳过发送 */
        $skipPrefixes = array_filter(array_map('trim', explode(',', $_SERVER['SMS_SKIP_SEND_PREFIXES'] ?? '')));
        foreach ($skipPrefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($verifyKey, $prefix)) {
                return;
            }
        }

        /** 指定手机号跳过发送 */
        $skipPhones = array_filter(array_map('trim', explode(',', $_SERVER['SMS_SKIP_SEND_PHONES'] ?? '')));
        if (in_array($verifyKey, $skipPhones, true)) {
            return;
        }


        /**
         * 直接从 Redis 获取验证码缓存和尝试次数
         * @var VerifyCodeCacheDto $cache
         * @var AttemptCacheDto $attempts
         */
        $cache      = $this->redis->getCache(VerifyCodeCacheDto::class, $verifyKey);
        $attempts   = $this->redis->getCache(AttemptCacheDto::class, $verifyKey);

        /** 缓存不存在：可能是已使用或过期 */
        if (is_null($cache)) {
            $this->logger->warning('验证码验证失败 - 缓存不存在', [
                'verify_key' => substr($verifyKey, 0, 8) . '...',
                'reason'      => 'verify_code_not_found',
            ]);
            throw new ValidatorParamsException(message: '验证码错误或已过期');
        }

        /** 验证类型必须是 captcha */
        if (!in_array($cache->type, ['captcha', 'sms'])) {
            throw new ValidatorParamsException(message: '验证码类型错误');
        }

        /** 检查验证次数限制（防止暴力破解） */
        if ($attempts && $attempts->limit >= 3) {
            $this->logger->warning('验证码验证失败 - 尝试次数过多', [
                'verify_key' => substr($verifyKey, 0, 8) . '...',
                'attempts'   => $attempts->limit,
            ]);
            throw new ValidatorParamsException(message: '验证码尝试次数过多，请重新获取');
        }

        /** 图形验证码：不区分大小写 */
        $isValid = $cache->phrase === strtoupper($code);

        if ($isValid) {
            /** 验证成功：删除验证码（一次性消费） */
            $this->redis->delCache(new VerifyCodeCacheDto($verifyKey));
            $this->redis->delCache(new AttemptCacheDto($verifyKey));

            $this->logger->info('验证码验证成功', [
                'verify_key'    => substr($verifyKey, 0, 8) . '...',
                'code_length'   => strlen($code),
                'consumed'      => true,
            ]);
        } else {
            /** 验证失败：增加尝试次数 */
            $newAttempts = ($attempts ? $attempts->limit : 0) + 1;
            $attemptCache = new AttemptCacheDto($verifyKey);
            $attemptCache->limit = $newAttempts;
            $this->redis->setCache($attemptCache);

            /** 记录到日志但不清除缓存，可以重试 */
            $this->logger->warning('验证码验证失败 - 验证码不匹配', [
                'verify_key' => substr($verifyKey, 0, 8) . '...',
                'attempts'   => $newAttempts,
            ]);
            throw new ValidatorParamsException(message: '验证码错误');
        }
    }
}