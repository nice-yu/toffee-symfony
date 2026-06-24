<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * 参数错误
 */
class ValidatorParamsException extends AbstractLogicException
{
    /**
     * http 状态码 422
     * @var int
     */
    public $code = Response::HTTP_UNPROCESSABLE_ENTITY;

    public $message = '参数错误';
}
