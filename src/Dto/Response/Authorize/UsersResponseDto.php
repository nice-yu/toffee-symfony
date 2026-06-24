<?php
declare(strict_types=1);

namespace App\Dto\Response\Authorize;

use App\Dto\Transformer\AbstractResponseDto;
use App\Enum\Common\LevelEnum;
use App\Enum\Common\StateEnum;
use App\Utils\StringUtils;

/**
 * 用户响应 DTO
 */
class UsersResponseDto extends AbstractResponseDto
{
    public string $token = '';

    public string $phone      = '';
    public int    $level      = 0;
    public int    $status     = 0;
    public string $inviteCode = '';
    public string $levelName  = '';
    public string $statusName = '';
    public int    $loginTime  = 0;
    public string $loginDate  = '';

    /** profile */
    public string $nickname  = '';
    public string $avatarUrl = '';
    public string $realName  = '';
    public int    $gender    = 0;

    /** wallet */
    public string $balance       = '0.00';
    public string $frozen        = '0.00';
    public string $totalEarnings = '0.00';

    /** device */
    public string $deviceId   = '';
    public int    $deviceType = 4;
    public string $deviceName = '';
    public string $osVersion  = '';
    public string $appVersion = '';

    /**
     * @param array{token: string, cache: object} $data
     * @return self
     */
    public function trans(array $data): self
    {
        ['token' => $token, 'cache' => $cache] = $data;

        $this->token      = $token ?? '';
        $this->phone      = StringUtils::maskPhone($cache->phone ?? '');
        $this->level      = $cache->level ?? 0;
        $this->status     = $cache->status ?? 1;
        $this->inviteCode = $cache->inviteCode ?? '';

        /** 转为中文 */
        $this->levelName  = LevelEnum::local($this->level)?->zh ?? '';
        $this->statusName = StateEnum::local($this->status)?->zh ?? '';

        /** 登录时间 */
        $this->loginTime  = time();
        $this->loginDate  = date('Y-m-d H:i:s', $this->loginTime);

        /** profile */
        if ($cache->profile) {
            $this->nickname  = $cache->profile->nickname ?? '';
            $this->avatarUrl = $cache->profile->avatarUrl ?? '';
            $this->realName  = $cache->profile->realName ?? '';
            $this->gender    = $cache->profile->gender ?? 0;
        }

        /** wallet */
        if ($cache->wallet) {
            $this->balance       = $cache->wallet->balance ?? '0.00';
            $this->frozen        = $cache->wallet->frozen ?? '0.00';
            $this->totalEarnings = $cache->wallet->totalEarnings ?? '0.00';
        }

        /** device */
        if ($cache->device) {
            $this->deviceId   = $cache->device->deviceId ?? '';
            $this->deviceType = $cache->device->deviceType ?? 4;
            $this->deviceName = $cache->device->deviceName ?? '';
            $this->osVersion  = $cache->device->osVersion ?? '';
            $this->appVersion = $cache->device->appVersion ?? '';
        }

        return $this;
    }
}
