<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\TestEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * 测试事件订阅器
 * @uses TestEventSubscriber
 */
class TestEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            TestEvent::class => 'onTestEvent',
        ];
    }

    /**
     * @uses onTestEvent
     */
    public function onTestEvent(TestEvent $event): void
    {
        $this->logger->info('测试事件已触发', [
            'name'  => $event->dto->name,
            'score' => $event->dto->score,
        ]);
    }
}
