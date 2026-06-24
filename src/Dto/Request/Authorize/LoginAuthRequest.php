<?php
declare(strict_types=1);

namespace App\Dto\Request\Authorize;

use App\Dto\Transformer\AbstractRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 手机号验证码 + 登录、注册
 * 手机号 + 密码
 *
 */
class LoginAuthRequest extends AbstractRequestDto
{
    #[Assert\NotBlank(message: '手机号不能为空', groups: ['login', 'password'])]
    #[Assert\Regex(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确', groups: ['login', 'password'])]
    public string $phone = '';

    #[Assert\NotBlank(message: '验证码不能为空', groups: ['login'])]
    public string $code = '';

    #[Assert\NotBlank(message: '密码不能为空', groups: ['password'])]
    public string $password = '';

    #[Assert\NotBlank(message: '登录方式不能为空', groups: ['login', 'password'])]
    #[Assert\Choice(choices: [1, 2, 3], message: '登录方式必须是 1, 2, 3')]
    public int $loginType = 1;

    public string $inviteCode = '';
}
