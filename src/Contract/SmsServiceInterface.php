<?php
declare(strict_types=1);

namespace App\Contract;

/**
 * 短信服务接口
 */
interface SmsServiceInterface
{
    /**
     * 发送短信验证码
     *
     * @param string $phone 手机号
     * @param string $code  验证码
     * @return bool 发送成功返回 true
     */
    public function sendVerifyCode(string $phone, string $code): bool;
}
