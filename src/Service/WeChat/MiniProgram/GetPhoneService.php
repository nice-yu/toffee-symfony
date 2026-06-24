<?php
declare(strict_types=1);

namespace App\Service\WeChat\MiniProgram;

use App\Exceptions\ServiceException;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * 生成微信小程序码
 * @uses GetPhoneService
 */
class GetPhoneService extends AbstractService
{
    /** @noinspection PhpPropertyOnlyWrittenInspection */
    public function __construct(
        string $appId,
        string $secret,
        private LoggerInterface $logger,
    ) {
        parent::__construct($appId, $secret);
    }

    /**
     * @param string $code
     * @param string $accessToken
     * @return array
     */
    public function run(string $code, string $accessToken): array
    {
        $result = $this->HttpClient(
            "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$accessToken}",
            'POST',
            [
                'code' => $code,
            ]
        );

        try {
            return [
                'phone'     => $result['phone_info']['phoneNumber'] ?? '',
                'country'   => $result['phone_info']['countryCode'] ?? '',
            ];
        } catch (Exception $e) {
            $this->logger->error('获取微信手机号失败', ['error' => $e->getMessage()]);
            throw new ServiceException(message: '获取微信手机号错误');
        }
    }
}
