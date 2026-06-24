<?php
declare(strict_types=1);

namespace App\Message;

/**
 * 测试消息
 */
class TestMessage
{
    public function __construct(
        public string $content,
        public int    $userId,
    ) {}
}
