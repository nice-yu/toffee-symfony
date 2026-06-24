<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\NoticeMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * 通知消息处理器
 * @uses NoticeMessageHandler
 */
#[AsMessageHandler]
class NoticeMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(NoticeMessage $message): void
    {
        $this->logger->info('通知消息已处理', [
            'title' => $message->title,
            'body'  => $message->body,
        ]);
    }
}
