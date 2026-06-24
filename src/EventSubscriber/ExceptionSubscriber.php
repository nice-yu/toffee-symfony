<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exceptions\AbstractLogicException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 订阅异常错误信息，由 kernel.exception 事件触发，统一格式化错误响应。
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    /**
     * 当前环境如果是 local:
     * - 错误会直接显示
     * - 方便开发人员使用
     * 其他环境:
     * - httpStatus 会被修改为 200
     * - 但是内容上的 code 标记不会被更改
     * @noinspection PhpUnused
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // 记录异常日志
        $this->logger?->error($exception->getMessage(), [
            'exception' => $exception,
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
        ]);

        /** 本地环境下允许输出原始错误 */
        if (($_SERVER['APP_ENV_CONFIG'] ?? '') === 'local') {
            return;
        }

        $event->allowCustomResponseCode();

        $isBusinessException = $exception instanceof AbstractLogicException;

        $response = new JsonResponse([
            'code'      => $isBusinessException ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
            'message'   => $isBusinessException ? $exception->getMessage() : '系统内部错误',
            'errorCode' => $isBusinessException ? $exception->errorCode : 0,
        ]);
        $response->setStatusCode(Response::HTTP_OK);

        $event->setResponse($response);
    }
}
