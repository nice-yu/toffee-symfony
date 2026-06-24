<?php
declare(strict_types=1);

namespace App\Cache\Users;

/**
 * 用户扩展信息缓存
 */
class ProfileCacheDto
{
    public string $nickname  = '';
    public string $avatarUrl = '';
    public string $realName  = '';
    public int    $gender    = 0;
}
