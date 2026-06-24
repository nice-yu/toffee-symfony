<?php
declare(strict_types=1);

namespace App\Cache\Users;

use App\Cache\AbstractCacheDto;

/**
 * 用户缓存 DTO
 * @uses ProfileCacheDto
 * @uses WalletCacheDto
 * @uses DeviceCacheDto
 */
class UserCacheDto extends AbstractCacheDto
{
    protected string $cacheKey = 'user:%s';

    protected int $ttl = 60 * 60 * 24 * 3;

    public int    $id         = 0;
    public string $phone      = '';
    public int    $level      = 0;
    public int    $status     = 1;
    public string $inviteCode = '';
    public array $tokens      = [];

    public ProfileCacheDto $profile;
    public WalletCacheDto  $wallet;
    public DeviceCacheDto  $device;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->profile = new ProfileCacheDto();
        $this->wallet  = new WalletCacheDto();
        $this->device  = new DeviceCacheDto();
    }
}
