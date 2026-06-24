<?php
declare(strict_types=1);

namespace App\Message;

/**
 * 通知消息
 */
class NoticeMessage
{
    public function __construct(
        public string $title,
        public string $body,
    ) {}
}
