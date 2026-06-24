<?php
declare(strict_types=1);

namespace App\Controller\Authorize;

use App\Action\Authorize\PasswordLoginAction;
use App\Action\Authorize\PhoneAuthAction;
use App\Action\Authorize\WechatLoginAction;
use App\Attribute\ValidatorGroup;
use App\Dto\Request\Authorize\LoginAuthRequest;
use App\Dto\Request\Authorize\WechatLoginRequest;
use App\Dto\Response\Authorize\UsersResponseDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 认证控制器 — 登录/注册
 * @uses AuthorizeController
 */
#[Route('/auth', methods: ['POST'])]
class AuthorizeController
{
    /**
     * 手机号 + 短信验证码登录/注册二合一
     * @param LoginAuthRequest      $dto
     * @param PhoneAuthAction       $action
     * @param UsersResponseDto      $response
     * @return Response
     * @uses login
     */
    #[Route('/login')]
    #[ValidatorGroup(['login'])]
    public function login(
        LoginAuthRequest    $dto,
        PhoneAuthAction     $action,
        UsersResponseDto    $response,
    ): Response
    {
        $result = $action->run($dto);

        return $response->trans($result)->response();
    }

    /**
     * 手机号 + 密码登录
     * @param LoginAuthRequest      $dto
     * @param PasswordLoginAction   $action
     * @param UsersResponseDto      $response
     * @return Response
     * @uses password
     */
    #[Route('/password')]
    #[ValidatorGroup(['password'])]
    public function password(
        LoginAuthRequest    $dto,
        PasswordLoginAction $action,
        UsersResponseDto    $response,
    ): Response
    {
        $result = $action->run($dto);

        return $response->trans($result)->response();
    }

    /**
     * 微信小程序 openid 登录/注册
     * @param WechatLoginRequest    $dto
     * @param WechatLoginAction     $action
     * @param UsersResponseDto      $response
     * @return Response
     * @uses program
     */
    #[Route('/program')]
    public function program(
        WechatLoginRequest  $dto,
        WechatLoginAction   $action,
        UsersResponseDto    $response,
    ): Response
    {
        $result = $action->run($dto);

        return $response->trans($result)->response();
    }
}
