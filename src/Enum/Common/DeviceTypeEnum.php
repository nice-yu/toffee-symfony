<?php
declare(strict_types=1);

namespace App\Enum\Common;

use App\Attribute\EnumMeta;
use App\Enum\EnumMetaTrait;

/**
 * 设备类型枚举
 */
enum DeviceTypeEnum: int
{
    use EnumMetaTrait;

    #[EnumMeta(zh: 'iOS', en: 'ios')]
    case IOS = 1;

    #[EnumMeta(zh: 'Android', en: 'android')]
    case ANDROID = 2;

    #[EnumMeta(zh: 'Web', en: 'web')]
    case WEB = 3;

    #[EnumMeta(zh: '小程序', en: 'miniProgram')]
    case MINI_PROGRAM = 4;
}
