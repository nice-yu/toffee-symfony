<?php
declare(strict_types=1);

namespace App\Dto\Request\Authorize;

use App\Dto\Transformer\AbstractRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 微信小程序登录请求参数
 */
class WechatLoginRequest extends AbstractRequestDto
{
    #[Assert\NotBlank(message: '微信 code 不能为空')]
    public string $code = '';

    public string $inviteCode = '';

    public int $loginType = 3;
}
