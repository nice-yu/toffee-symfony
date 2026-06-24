<?php
declare(strict_types=1);

namespace App\Controller\Common;

use App\Action\Common\GenerateSmsCodeAction;
use App\Action\Common\GenerateImgCodeAction;
use App\Action\Common\VerifyCodeAction;
use App\Dto\Request\Common\SmsCodeRequestDto;
use App\Dto\Request\Common\VerifyCodeRequestDto;
use App\Dto\Response\Common\SuccessResponseDto;
use App\Dto\Response\Common\VerifyCodeResponseDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 验证码控制器 — 图形验证码 + 短信验证码
 * @uses VerifyCodeController
 */
#[Route('/common/verify', methods: ['POST'])]
class VerifyCodeController
{
    /**
     * 生成图形验证码
     * @param GenerateImgCodeAction $action
     * @param VerifyCodeResponseDto $response
     * @return Response
     * @uses imgCode
     */
    #[Route('/img-code')]
    public function imgCode(
        GenerateImgCodeAction   $action,
        VerifyCodeResponseDto   $response,
    ): Response
    {
        $result = $action->run();

        return $response->trans($result)->response();
    }

    /**
     * 发送短信验证码
     * @param SmsCodeRequestDto $dto
     * @param GenerateSmsCodeAction $action
     * @param SuccessResponseDto $response
     * @return Response
     * @uses smsCode
     */
    #[Route('/sms-code')]
    public function smsCode(
        SmsCodeRequestDto       $dto,
        GenerateSmsCodeAction   $action,
        SuccessResponseDto      $response,
    ): Response
    {
        $action->run($dto);

        return $response->response();
    }

    /**
     * 校验图形验证码
     * @param VerifyCodeRequestDto  $dto
     * @param VerifyCodeAction      $action
     * @param SuccessResponseDto    $response
     * @return Response
     */
    #[Route('/validate')]
    public function validate(
        VerifyCodeRequestDto    $dto,
        VerifyCodeAction        $action,
        SuccessResponseDto      $response,
    ): Response
    {
        $action->run($dto);

        return $response->response();
    }
}
