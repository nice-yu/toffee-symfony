<?php
declare(strict_types=1);

namespace App\Service\Sms;

use App\Contract\SmsServiceInterface;
use App\Exceptions\ServiceException;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * 短信服务责任链
 *
 * 优先使用阿里云，失败自动降级到腾讯云
 * @uses ChainSmsService
 */
class ChainSmsService implements SmsServiceInterface
{
    public function __construct(
        private AliCloudSmsService   $aliCloud,
        private TencentCloudService  $txCloud,
        private LoggerInterface      $logger,
    ) {}

    /**
     * 发送短信验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @return bool
     * @throws ServiceException 两个服务都失败时抛出
     */
    public function sendVerifyCode(string $phone, string $code): bool
    {
        try {

            /** 优先尝试阿里云 */
            $this->logger->info('短信验证码发送', [
                'type'  => 'AliCloudSms',
                'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
                'code'  => $code,
            ]);

            if ($this->aliCloud->sendVerifyCode($phone, $code)) {
                return true;
            }
        } catch (Exception $e) {
            $this->logger->error('阿里云短信发送异常，降级到腾讯云', [
                'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
                'error' => $e->getMessage(),
            ]);
        }

        try {

            /** 降级到腾讯云 */
            $this->logger->info('短信验证码发送', [
                'type'  => 'TencentCloud',
                'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
                'code'  => $code,
            ]);

            if ($this->txCloud->sendVerifyCode($phone, $code)) {
                return true;
            }
        } catch (Exception $e) {
            $this->logger->error('腾讯云短信发送异常，无可用服务商', [
                'phone' => substr($phone, 0, 3) . '****' . substr($phone, -4),
                'error' => $e->getMessage(),
            ]);
        }

        throw new ServiceException(message: '短信发送失败，请稍后重试');
    }
}
