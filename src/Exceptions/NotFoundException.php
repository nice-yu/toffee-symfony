<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * 无资源错误
 */
class NotFoundException extends AbstractLogicException
{
    /**
     * http 状态码 404
     * @var int
     */
    public $code = Response::HTTP_NOT_FOUND;

    public $message = '无资源';
}
