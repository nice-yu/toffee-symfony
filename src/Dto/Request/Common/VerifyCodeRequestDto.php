<?php
declare(strict_types=1);

namespace App\Dto\Request\Common;

use App\Dto\Transformer\AbstractRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 验证码请求 DTO
 */
class VerifyCodeRequestDto extends AbstractRequestDto
{
    #[Assert\NotBlank(message: '验证码 key 不能为空')]
    public string $key = '';

    #[Assert\NotBlank(message: '验证码不能为空')]
    public string $code = '';

    #[Assert\Choice(choices: ['captcha', 'sms'], message: '验证类型必须是 captcha、sms')]
    public string $type = 'captcha';
}
