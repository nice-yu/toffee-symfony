<?php
declare(strict_types=1);

namespace App\Task\Authorize;

use App\Entity\UserDevice;
use App\Entity\UserLoginLog;
use App\Entity\UserProfile;
use App\Entity\Users;
use App\Entity\UserWallet;
use App\Entity\UserWechat;
use App\Exceptions\ServiceException;
use App\Repository\UserRepository;
use App\Utils\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * 注册 Task — 创建用户 + profile + wallet + device + login_log + 微信绑定
 *
 * 适用：手机号验证码注册、微信小程序注册
 * @uses UserRegisterTask
 */
class UserRegisterTask
{
    private string $phone      = '';
    private string $inviteCode = '';
    private string $openid     = '';
    private string $unionid    = '';

    public function __construct(
        private RequestStack            $requestStack,
        private LoggerInterface         $logger,
        private UserRepository          $userRepository,
        private EntityManagerInterface  $entityManager,
    ) {}

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setInviteCode(string $inviteCode): self
    {
        $this->inviteCode = $inviteCode;
        return $this;
    }

    public function setOpenid(string $openid): self
    {
        $this->openid = $openid;
        return $this;
    }

    public function setUnionid(string $unionid): self
    {
        $this->unionid = $unionid;
        return $this;
    }

    /**
     * 获取设备信息
     */
    private function header(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        return [
            'deviceType'  => (int) $request->headers->get('Device-Type', '4'),
            'deviceId'    => $request->headers->get('Device-Id', ''),
            'deviceName'  => $request->headers->get('Device-Name', ''),
            'osVersion'   => $request->headers->get('OS-Version', ''),
            'appVersion'  => $request->headers->get('App-Version', ''),
            'userAgent'   => $request->headers->get('User-Agent', ''),
            'clientIp'    => $request->getClientIp() ?? '',
        ];
    }

    /**
     * 生成邀请码
     */
    private function getInviteCode(): string
    {
        $code = StringUtils::inviteCode();
        $resp = $this->userRepository->findOneBy(['inviteCode' => $code]);
        if (is_null($resp)) {
            return $code;
        }
        return $this->getInviteCode();
    }

    /**
     * 获取邀请人 ID
     */
    private function getParentId(): int
    {
        if ($this->inviteCode === '') {
            return 0;
        }
        $parent = $this->userRepository->findOneBy(['inviteCode' => $this->inviteCode]);
        if (is_null($parent)) {
            return 0;
        }

        return $parent->getId();
    }

    /**
     * @param int $loginType 登录方式：1=手机号验证码, 2=手机密码, 3=微信 openid
     * @return Users
     */
    public function run(int $loginType): Users
    {
        $this->entityManager->beginTransaction();

        try {
            /** 随机密码 + 盐 */
            $salt     = bin2hex(random_bytes(16));
            $password = bin2hex(random_bytes(8));
            $password = password_hash($password . $salt, PASSWORD_BCRYPT);
            $selfCode = $this->getInviteCode();
            $parentId = $this->getParentId();

            /** 创建用户 */
            $userInfo = new Users();
            if ($this->phone !== '') {
                $userInfo->setPhone($this->phone);
            }
            $userInfo->setSalt($salt);
            $userInfo->setPassword($password);
            $userInfo->setInviteCode($selfCode);
            $userInfo->setParentId($parentId);

            $this->entityManager->persist($userInfo);
            $this->entityManager->flush();

            $userId = $userInfo->getId();

            /** 创建 user_profile：手机号取后四位，微信随机四字母 */
            $profile = new UserProfile();
            $profile->setUserId($userId);
            if ($this->phone !== '') {
                $profile->setNickname('会员' . substr($this->phone, -4));
            } else {
                $profile->setNickname('微信用户' . StringUtils::random(4));
            }
            $this->entityManager->persist($profile);

            /** 创建 user_wallet */
            $wallet = new UserWallet();
            $wallet->setUserId($userId);
            $this->entityManager->persist($wallet);

            /** 创建设备记录 */
            $header = $this->header();
            $device = new UserDevice();
            $device->setUserId($userId);
            $device->setDeviceType($header['deviceType']);
            $device->setDeviceId($header['deviceId']);
            $device->setDeviceName($header['deviceName']);
            $device->setOsVersion($header['osVersion']);
            $device->setAppVersion($header['appVersion']);
            $this->entityManager->persist($device);

            /** 记录登录日志 */
            $log = new UserLoginLog();
            $log->setUserId($userId);
            $log->setLoginType($loginType);
            $log->setDeviceType($header['deviceType']);
            $log->setIpAddress($header['clientIp']);
            $log->setUserAgent($header['userAgent']);
            $this->entityManager->persist($log);

            /** 微信注册：绑定 openid */
            if ($this->openid !== '') {
                $wechat = new UserWechat();
                $wechat->setUserId($userId);
                $wechat->setOpenid($this->openid);
                $wechat->setUnionid($this->unionid);
                $this->entityManager->persist($wechat);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->logger->info('用户注册成功', [
                'user_id'    => $userId,
                'login_type' => $loginType,
                'phone'      => $this->phone !== '' ? substr($this->phone, 0, 3) . '****' . substr($this->phone, -4) : '微信用户',
            ]);

            return $userInfo;
        } catch (Exception $e) {
            if ($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            $this->logger->error('用户注册失败', [
                'phone' => $this->phone !== '' ? substr($this->phone, 0, 3) . '****' . substr($this->phone, -4) : '微信用户',
                'error' => $e->getMessage(),
            ]);
            throw new ServiceException(message: '注册失败，请稍后重试');
        }
    }

}
