<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * 权限错误
 */
class NoPermissionException extends AbstractLogicException
{
    /**
     * http 状态码 403
     * @var int
     */
    public $code = Response::HTTP_FORBIDDEN;

    public $message = '无权限';
}
