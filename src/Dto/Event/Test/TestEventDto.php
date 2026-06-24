<?php
declare(strict_types=1);

namespace App\Dto\Event\Test;

/**
 * 测试事件载荷 DTO
 */
class TestEventDto
{
    public function __construct(
        public string $name,
        public int    $score,
    ) {}
}
