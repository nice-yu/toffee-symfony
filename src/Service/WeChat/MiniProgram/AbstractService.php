<?php
declare(strict_types=1);

namespace App\Service\WeChat\MiniProgram;

use App\Exceptions\ServiceException;
use App\Utils\HttpClientUtils;

/**
 * 微信小程序配置
 */
class AbstractService
{
    public function __construct(
        public string $appId,
        public string $secret
    ) {}

    /**
     * 发起请求
     * @param string $url       请求地址
     * @param string $method    请求方式
     * @param array $params       请求内容
     * @return array|null
     */
    public function HttpClient(string $url, string $method, array $params): ?array
    {
        $result = HttpClientUtils::httpClient($url, $method, $params);

        /** 返回 null */
        if (is_null($result)) {
            return null;
        }

        /** 判断错误 */
        if ((isset($result['errcode']) && $result['errcode'] !== 0)) {
            throw new ServiceException(message: "{$result['errcode']} : {$result['errmsg']}");
        }

        return $result;
    }
}
