<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * 业务异常基类
 *
 * 继承 RuntimeException 而非 LogicException：
 * LogicException 在 Symfony 中会被当作编程错误，触发 request.CRITICAL 日志，
 * 导致 ExceptionSubscriber 处理前多出一条重复日志。
 * RuntimeException 被视为运行时异常，由 kernel.exception 统一接管。
 */
class AbstractLogicException extends RuntimeException
{
    /**
     * 状态码 500
     * @var int
     */
    public $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    /**
     * 错误信息
     * @var string
     */
    public $message = '内部服务错误';

    /**
     * 错误对照码
     * @var int
     */
    public int $errorCode = 0;

    /**
     * @param int|null    $errorCode 错误码
     * @param string|null $message   错误信息
     * @param int|null    $code      web 状态码
     * @param \Throwable|null $previous 前驱异常
     */
    public function __construct(?int $errorCode = null, ?string $message = null, ?int $code = null, ?Throwable $previous = null)
    {
        $code    = $code ?? $this->code;
        $message = $message ?? $this->message;

        parent::__construct($message, $code, $previous);

        $this->code      = $code;
        $this->message   = $message;
        $this->errorCode = $errorCode ?? $this->errorCode;
    }
}
