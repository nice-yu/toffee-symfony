<?php
declare(strict_types=1);

namespace App\Task\Authorize;

use App\Cache\Users\UserCacheDto;
use App\Entity\Users;
use App\Exceptions\ServiceException;
use App\Utils\RedisUtils;
use App\Utils\Token\TokenUtils;
use Psr\Log\LoggerInterface;

/**
 * 更新用户缓存 Task
 * @uses UpdateUserCacheTask
 */
class UpdateUserCacheTask
{
    private bool    $removeToken = false;
    private ?string $token       = null;
    private int     $deviceType  = 4;

    public function __construct(
        private RedisUtils      $redisUtils,
        private TokenUtils      $tokenUtils,
        private LoggerInterface $logger,
    ) {}

    /**
     * 将 Users（含 profile/wallet/device）写入 Redis 缓存
     *
     * @param Users $user 附带 ->profile、->wallet、->device
     * @return UserCacheDto|null
     */
    public function run(Users $user): ?UserCacheDto
    {
        $userId = $user->getId();

        /** 优先取已有缓存，不存在则新建 */
        $cache = $this->redisUtils->getCache(UserCacheDto::class, $userId);
        if (is_null($cache)) {
            $cache = new UserCacheDto($userId);
        } else {
            /** 已有缓存则校验旧 token 有效性，剔除过期的 */
            $cache->tokens = $this->checkToken($cache->tokens);
        }

        /** 实体 → CacheDto */
        $cache = $this->entityTransCache($user, $cache);

        /** 新 token 合并到 tokens */
        if ($this->token !== null && !$this->removeToken) {
            $cache->tokens[$this->deviceType] = $this->token;
        }

        /** 写入 Redis */
        if (!$this->redisUtils->setCache($cache)) {
            $this->logger->error('用户缓存写入失败 Redis 错误', ['user_id' => $userId]);
            throw new ServiceException(message: '服务错误, 请重试');
        }

        return $cache;
    }

    /**
     * 由实体转为 CacheDto
     * @param Users $users
     * @param UserCacheDto $cache
     * @return UserCacheDto
     */
    private function entityTransCache(Users $users, UserCacheDto $cache): UserCacheDto
    {
        /** 写入 Users 核心字段 */
        $cache->id         = $users->getId();
        $cache->phone      = $users->getPhone() ?? '';
        $cache->level      = $users->getLevel();
        $cache->status     = $users->getStatus();
        $cache->inviteCode = $users->getInviteCode() ?? '';

        /** 写入 user_profile 扩展信息 */
        if ($users->getProfile()) {
            $cache->profile->nickname  = $users->getProfile()->getNickname();
            $cache->profile->avatarUrl = $users->getProfile()->getAvatarUrl();
            $cache->profile->realName  = $users->getProfile()->getRealName();
            $cache->profile->gender    = $users->getProfile()->getGender();
        }

        /** 写入 user_wallet 钱包信息 */
        if ($users->getWallet()) {
            $cache->wallet->balance       = $users->getWallet()->getBalance();
            $cache->wallet->frozen        = $users->getWallet()->getFrozen();
            $cache->wallet->totalEarnings = $users->getWallet()->getTotalEarnings();
        }

        /** 写入 user_device 设备信息 */
        if ($users->getDevice()) {
            $cache->device->deviceId   = $users->getDevice()->getDeviceId() ?? '';
            $cache->device->deviceType = $users->getDevice()->getDeviceType();
            $cache->device->deviceName = $users->getDevice()->getDeviceName();
            $cache->device->osVersion  = $users->getDevice()->getOsVersion();
            $cache->device->appVersion = $users->getDevice()->getAppVersion();
        }

        return $cache;
    }

    /**
     * 校验旧 token 有效性，剔除过期的
     */
    private function checkToken(array $tokens): array
    {
        $result = [];
        foreach ($tokens as $item) {
            $token = $this->tokenUtils->verify($item);
            if ($token !== null) {
                $result[$token->device] = $item;
            }
        }
        return $result;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function setDeviceType(int $deviceType): self
    {
        $this->deviceType = $deviceType;
        return $this;
    }
}
