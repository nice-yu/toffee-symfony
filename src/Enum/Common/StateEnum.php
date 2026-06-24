<?php
declare(strict_types=1);

namespace App\Enum\Common;

use App\Attribute\EnumMeta;
use App\Enum\EnumMetaTrait;

/**
 * 通用状态枚举
 */
enum StateEnum: int
{
    use EnumMetaTrait;

    /**
     * 禁用
     */
    #[EnumMeta(zh: '禁用', en: 'disabled')]
    case DISABLED = 0;

    /**
     * 正常
     */
    #[EnumMeta(zh: '正常', en: 'normal')]
    case NORMAL = 1;

    /**
     * 封禁
     */
    #[EnumMeta(zh: '封禁', en: 'closed')]
    case CLOSED = 2;
}
