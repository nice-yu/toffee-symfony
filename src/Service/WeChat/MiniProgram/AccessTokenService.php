<?php
declare(strict_types=1);

namespace App\Service\WeChat\MiniProgram;

use App\Cache\WeChat\AccessTokenCacheDto;
use App\Exceptions\ServiceException;
use App\Utils\RedisUtils;

/**
 * 生成微信小程序码
 * @uses AccessTokenService
 */
class AccessTokenService extends AbstractService
{
    public function __construct(
        string $appId,
        string $secret,
        private RedisUtils $redisUtils,
    ) {
        parent::__construct($appId, $secret);
    }

    /**
     * 获取到微信小程序 AccessToken
     * @return string
     */
    public function run(): string
    {
        /**
         * 查看是否存在缓存
         * @var AccessTokenCacheDto $cache
         */
        $cache = $this->redisUtils->getCache(AccessTokenCacheDto::class, $this->appId);
        if(!is_null($cache)) {
            return $cache->accessToken;
        }

        /** 在线获取 token */
        $result = $this->HttpClient(
            'https://api.weixin.qq.com/cgi-bin/stable_token',
            'POST',
            [
                'grant_type' => 'client_credential',
                'appid'      => $this->appId,
                'secret'     => $this->secret,
            ]
        );

        /** 存储至 redis */
        $cache = new AccessTokenCacheDto($this->appId);
        $cache->accessToken = $result['access_token'];

        /** 存储时间: 官方时间减 一分钟 */
        if (!$this->redisUtils->setCache($cache)){
            throw new ServiceException(message: 'redis 设置错误问题');
        }

        return $cache->accessToken;
    }
}
