<?php
declare(strict_types=1);

namespace App\Service\WeChat\MiniProgram;

use App\Exceptions\ServiceException;

/**
 * 获取微信用户登录态
 * @uses JsCodeService
 */
class JsCodeService extends AbstractService
{
    /**
     * 获取到微信小程序用户登录态
     * @param string $code
     * @return array
     */
    public function run(string $code): array
    {
        /** 获取用户 unionid、openid */
        $result = $this->HttpClient(
            'https://api.weixin.qq.com/sns/jscode2session',
            'GET',
            [
                'appid'      => $this->appId,
                'secret'     => $this->secret,
                'js_code'    => $code,
                'grant_type' => 'authorization_code',
            ]
        );

        if ($result === null) {
            throw new ServiceException(message: '微信服务暂不可用');
        }

        return $result;
    }
}
