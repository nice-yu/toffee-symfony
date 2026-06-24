<?php
declare(strict_types=1);

namespace App\Dto\Request\Common;

use App\Dto\Transformer\AbstractRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 短信验证码请求参数
 */
class SmsCodeRequestDto extends AbstractRequestDto
{
    #[Assert\NotBlank(message: '手机号不能为空')]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
    public string $phone = '';
}
