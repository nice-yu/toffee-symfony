<?php
declare(strict_types=1);

namespace App\Security;

use App\Cache\Users\UserCacheDto;
use App\Exceptions\NoPermissionException;
use App\Utils\RedisUtils;
use App\Utils\Token\TokenUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * 用户认证器 — Token 校验 + 缓存验证
 * @uses UserAuthenticator
 */
class UserAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private TokenUtils   $tokenUtils,
        private RedisUtils   $redisUtils,
        private UserProvider $userProvider,
    ) {}

    /**
     * 判断当前请求是否需要认证
     */
    public function supports(Request $request): ?bool
    {
        if ($request->attributes->get('anonymous')) {
            return false;
        }

        return true;
    }

    /**
     * 执行认证
     */
    public function authenticate(Request $request): Passport
    {
        /** 获取 Token */
        $credentials = $request->headers->get('Auth-Token');
        if (empty($credentials)) {
            throw new NoPermissionException(message: '请先登录');
        }

        /** 去掉 Bearer 前缀（兼容无前缀的情况） */
        if (str_starts_with($credentials, 'Bearer ')) {
            $credentials = substr($credentials, 7);
        }

        /** 解析 Token */
        $token = $this->tokenUtils->verify($credentials);
        if (is_null($token)) {
            throw new NoPermissionException(message: '登录过期');
        }

        /**
         * 校验缓存
         * @var UserCacheDto $cache
         */
        $cache = $this->redisUtils->getCache(UserCacheDto::class, (string) $token->id);
        if (is_null($cache)) {
            throw new NoPermissionException(message: '请重新登录');
        }

        return new SelfValidatingPassport(new UserBadge((string) $token->id,
            function () use ($token, $cache, $credentials) {
                return $this->userProvider
                    ->setTokenDto($token)
                    ->setCredentials($credentials)
                    ->setUserCache($cache);
            }
        ));
    }

    /**
     * 认证成功
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * 认证失败
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
