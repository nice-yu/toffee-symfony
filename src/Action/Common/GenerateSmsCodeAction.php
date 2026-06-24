<?php
declare(strict_types=1);

namespace App\Action\Common;

use App\Cache\Common\VerifyCodeCacheDto;
use App\Contract\SmsServiceInterface;
use App\Dto\Request\Common\SmsCodeRequestDto;
use App\Exceptions\ServiceException;
use App\Utils\RedisUtils;
use Psr\Log\LoggerInterface;

/**
 * 生成短信验证码 Action
 * @uses GenerateSmsCodeAction
 */
class GenerateSmsCodeAction
{
    public function __construct(
        private RedisUtils          $redisUtils,
        private SmsServiceInterface $smsService,
        private LoggerInterface     $logger,
    ) {}

    /**
     * 生成 6 位短信验证码，存入 Redis，调用短信服务发送
     *
     * @param SmsCodeRequestDto $dto
     * @return bool
     */
    public function run(SmsCodeRequestDto $dto): bool
    {
        /** 号段跳过发送 */
        $skipPrefixes = array_filter(array_map('trim', explode(',', $_SERVER['SMS_SKIP_SEND_PREFIXES'] ?? '')));
        foreach ($skipPrefixes as $prefix) {
            if ($prefix !== '' && str_starts_with($dto->phone, $prefix)) {
                return true;
            }
        }

        /** 指定手机号跳过发送 */
        $skipPhones = array_filter(array_map('trim', explode(',', $_SERVER['SMS_SKIP_SEND_PHONES'] ?? '')));
        if (in_array($dto->phone, $skipPhones, true)) {
            return true;
        }

        /** 生成 4 位数字验证码 */
        $code = (string)rand(1000, 9999);

        /** 验证码答案存入 Redis */
        $cache = new VerifyCodeCacheDto($dto->phone);
        $cache->phrase = $code;
        $cache->type   = 'sms';

        if (!$this->redisUtils->setCache($cache)) {
            throw new ServiceException(message: 'redis 链接出现错误');
        }

        /** 调用短信服务发送验证码 */
        $this->smsService->sendVerifyCode($dto->phone, $code);

        /** 记录操作日志 */
        $this->logger->info('短信验证码发送成功', [
            'phone' => substr($dto->phone, 0, 3) . '****' . substr($dto->phone, -4),
            'code'  => $code,
        ]);

        return true;
    }
}
