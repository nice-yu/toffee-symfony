<?php
declare(strict_types=1);

namespace App\Service\Sms;

use App\Contract\SmsServiceInterface;
use App\Exceptions\ServiceException;

/**
 * 腾讯云发送短信
 * @uses TencentCloudService
 */
class TencentCloudService implements SmsServiceInterface
{
    /**
     * 发送短信验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @return bool
     * @throws ServiceException
     */
    public function sendVerifyCode(string $phone, string $code): bool
    {
        /** TODO: 接入真实短信服务商后替换为实际调用 */
        return true;
    }
}