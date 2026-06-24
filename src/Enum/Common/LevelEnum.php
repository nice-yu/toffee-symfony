<?php
declare(strict_types=1);

namespace App\Enum\Common;

use App\Attribute\EnumMeta;
use App\Enum\EnumMetaTrait;

enum LevelEnum: int
{
    use EnumMetaTrait;

    /** 随省新秀（未消费用户） */
    #[EnumMeta(zh: '随省新秀', en: 'rookie')]
    case ROOKIE = 1;

    /** 随省达人（已消费用户） */
    #[EnumMeta(zh: '随省达人', en: 'expert')]
    case EXPERT = 2;
}