<?php
declare(strict_types=1);

namespace App\Task\Authorize;

use App\Entity\UserLoginLog;
use App\Entity\Users;
use App\Repository\UserRepository;
use App\Utils\Token\TokenUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 用户登录 Task — 加载关联 + 更新设备 + 生成 Token + 写缓存 + 登录日志
 * @uses UserLoginTask
 */
class UserLoginTask
{
    public function __construct(
        private RequestStack            $requestStack,
        private UserRepository          $userRepository,
        private TokenUtils              $tokenUtils,
        private UpdateUserCacheTask     $cacheTask,
        private EntityManagerInterface  $entityManager,
        private LoggerInterface         $logger,
    ) {}

    /**
     * @param Users $userInfo 已通过凭证校验的用户
     * @param int   $loginType 登录方式：1=手机验证码, 2=手机密码, 3=微信 openid
     * @return array{token: string, cache: object}
     */
    public function run(Users $userInfo, int $loginType = 2): array
    {
        $request    = $this->requestStack->getCurrentRequest();
        $deviceType = (int) $request->headers->get('Device-Type', '4');

        /** 加载关联数据 */
        $userInfo   = $this->userRepository->findUserWithRelations($userInfo->getId(), ['up', 'uw', 'ud']);

        /** 更新设备信息 */
        if ($userInfo->getDevice()) {
            $userInfo->getDevice()->setDeviceId($request->headers->get('Device-Id', ''));
            $userInfo->getDevice()->setDeviceType($deviceType);
            $userInfo->getDevice()->setDeviceName($request->headers->get('Device-Name', ''));
            $userInfo->getDevice()->setOsVersion($request->headers->get('OS-Version', ''));
            $userInfo->getDevice()->setAppVersion($request->headers->get('App-Version', ''));
            $this->entityManager->persist($userInfo->getDevice());
        }

        /** 记录登录日志 */
        $log = new UserLoginLog();
        $log->setUserId($userInfo->getId());
        $log->setLoginType($loginType);
        $log->setDeviceType($deviceType);
        $log->setIpAddress($request->getClientIp() ?? '');
        $log->setUserAgent($request->headers->get('User-Agent', ''));
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        /** 生成 Token */
        $expire = time() + (86400 * 3);
        $token  = $this->tokenUtils->setId($userInfo->getId())->setDeviceType($deviceType)->setExpire($expire)->generate();

        /** 写入缓存 */
        $cache = $this->cacheTask->setToken($token)->setDeviceType($deviceType)->run($userInfo);

        $this->logger->info('用户登录成功', [
            'user_id'     => $userInfo->getId(),
            'phone'       => substr($userInfo->getPhone() ?? '', 0, 3) . '****' . substr($userInfo->getPhone() ?? '', -4),
            'device_type' => $deviceType,
        ]);

        return ['token' => $token, 'cache' => $cache];
    }
}
