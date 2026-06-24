<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * 业务逻辑错误
 */
class BusinessException extends AbstractLogicException
{
    /**
     * http 状态码 400
     * @var int
     */
    public $code = Response::HTTP_BAD_REQUEST;

    public $message = '业务处理失败';
}
