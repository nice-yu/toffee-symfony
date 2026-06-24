<?php
declare(strict_types=1);

namespace App\Cache\Users;

/**
 * 用户钱包缓存
 */
class WalletCacheDto
{
    public string $balance       = '0.00';
    public string $frozen        = '0.00';
    public string $totalEarnings = '0.00';
}
