<?php
declare(strict_types=1);

namespace App\Action\Common;

use App\Dto\Request\Common\VerifyCodeRequestDto;
use App\Task\Common\VerifyCodeTask;
use Psr\Log\LoggerInterface;

/**
 * 校验验证码 Action - 支持图形验证码、短信验证码等
 * @uses VerifyCodeAction
 */
class VerifyCodeAction
{
    public function __construct(
        private VerifyCodeTask  $task,
        private LoggerInterface $logger,
    ) {}

    public function run(VerifyCodeRequestDto $dto): void
    {
        /** 验证验证码 */
        $this->task->run($dto->key, $dto->code);

        /** 记录验证日志 */
        $this->logger->info('图形验证码验证请求', [
            'verify_key' => substr($dto->key, 0, 8) . '...',
            'code_length' => strlen($dto->code),
        ]);
    }
}
