<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * 内部错误
 */
class ServiceException extends AbstractLogicException
{
    /**
     * http 状态码 500
     * @var int
     */
    public $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    public $message = '服务错误';
}
