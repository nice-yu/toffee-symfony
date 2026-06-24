<?php
declare(strict_types=1);

namespace App\Service\WeChat\MiniProgram;

use App\Exceptions\BusinessException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * 生成微信小程序码
 * @uses GenerateQrCodeService
 */
class GenerateQrCodeService extends AbstractService
{
    private string $scene = '';
    private string $page  = '';

    public function __construct(
        string $appId,
        string $secret,
        private LoggerInterface $logger,
    ) {
        parent::__construct($appId, $secret);
    }

    /** @uses setScene */
    public function setScene(string $scene): self
    {
        $this->scene = $scene;
        return $this;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;
        return $this;
    }

    /**
     * 生成二维码
     *
     * @param string $accessToken 小程序 access_token
     * @return string Base64编码的小程序码图片
     * @throws BusinessException 生成失败时抛出
     */
    public function run(string $accessToken): string
    {
        /** 识别当前环境情况, 决定生成的参数信息 */
        $merge = ['check_path' => true, 'env_version' => 'release'];
        if($_ENV['APP_ENV_CONFIG'] == 'test') {
            $merge = ['check_path' => false, 'env_version' => 'trial'];
        } else if($_ENV['APP_ENV_CONFIG'] == 'dev') {
            $merge = ['check_path' => false, 'env_version' => 'develop'];
        }

        try {
            /** 发起请求信息 */
            $client     = HttpClient::create(['verify_peer' => false, 'http_version' => '1.1']);
            $response   = $client->request(
                'POST',
                "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$accessToken}",
                ['json'  => array_merge([
                    'scene' => $this->scene,
                    'page'  => $this->page,
                    'width' => 280
                ], $merge)]
            );

            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                throw new BusinessException(message: '生成小程序码 状态错误');
            }
            /** 获取到图片 buffer */
            $buffer = $response->getContent(false);
            if(empty($buffer)) {
                throw new BusinessException(message: '生成小程序码 buffer 为空');
            }

            return base64_encode($buffer);
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $this->logger->error('生成小程序码失败', ['error' => $e->getMessage()]);
            throw new BusinessException(message: '生成小程序推广码错误');
        }
    }
}
