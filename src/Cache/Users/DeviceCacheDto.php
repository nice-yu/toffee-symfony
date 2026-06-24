<?php
declare(strict_types=1);

namespace App\Cache\Users;

/**
 * 用户设备缓存
 */
class DeviceCacheDto
{
    public string $deviceId   = '';
    public int    $deviceType = 4;
    public string $deviceName = '';
    public string $osVersion  = '';
    public string $appVersion = '';
}
