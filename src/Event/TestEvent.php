<?php
declare(strict_types=1);

namespace App\Event;

use App\Dto\Event\Test\TestEventDto;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * 测试事件
 */
class TestEvent extends Event
{
    public function __construct(
        public readonly TestEventDto $dto,
    ) {}
}
