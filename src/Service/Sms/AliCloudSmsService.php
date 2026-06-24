<?php
declare(strict_types=1);

namespace App\Service\Sms;

use AlibabaCloud\Dara\Models\RuntimeOptions;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use App\Contract\SmsServiceInterface;
use App\Exceptions\ServiceException;
use Darabonba\OpenApi\Models\Config;
use Exception;

/**
 * 阿里云发送短信
 * @uses AliCloudSmsService
 */
class AliCloudSmsService implements SmsServiceInterface
{
    public function __construct(
        private string $appId,
        private string $secret,
        private string $name,
        private string $tempId,
    ) {}

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
        try {
            $config = new Config([
                "accessKeyId"     => $this->appId,
                "accessKeySecret" => $this->secret,
                "endpoint"        => "dysmsapi.aliyuncs.com"
            ]);

            $client = new Dysmsapi($config);

            $request = new SendSmsRequest([
                "phoneNumbers"  => $phone,
                "signName"      => $this->name,
                "templateCode"  => $this->tempId,
                "templateParam" => json_encode(['code' => $code])
            ]);

            /** 设置运行时选项 */
            $runtime = new RuntimeOptions([]);

            /** 未配置CA证书，临时忽略SSL验证 */
            $runtime->ignoreSSL = true;

            /** 发送短信 */
            $response = $client->sendSmsWithOptions($request, $runtime);
            if ($response->body->code === 'OK') {
                return true;
            } else {
                return false;
            }
        } catch (Exception) {
            return false;
        }
    }
}