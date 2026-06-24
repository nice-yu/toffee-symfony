<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\TestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * 测试消息处理器
 * @uses TestMessageHandler
 */
#[AsMessageHandler]
class TestMessageHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function __invoke(TestMessage $message): void
    {
        $this->logger->info('测试消息已处理', [
            'content' => $message->content,
            'userId'  => $message->userId,
        ]);
    }
}
