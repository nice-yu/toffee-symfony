<?php
declare(strict_types=1);

namespace App\Dto\Response\Common;

use App\Dto\Transformer\AbstractResponseDto;

class TableResponseDto extends AbstractResponseDto
{
    public int $total = 0;
    public int $cursor = 0;
    public bool $hasMore = false;
    public int $pageCount = 0;
    public array $items = [];

    /** 预留扩展字段 1 */
    public string $common1 = '';
    /** 预留扩展字段 2 */
    public string $common2 = '';
    /** 预留扩展字段 3 */
    public string $common3 = '';
    /** 预留扩展字段 4 */
    public string $common4 = '';
}
