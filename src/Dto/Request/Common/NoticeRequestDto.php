<?php
declare(strict_types=1);

namespace App\Dto\Request\Common;

use App\Dto\Transformer\AbstractRequestDto;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * 微信小程序通知请求参数
 */
class NoticeRequestDto extends AbstractRequestDto
{
    #[Assert\NotBlank(message: '消息模板 ID 不能为空', groups: ['miniProgram'])]
    public string $tempId = '';

    #[Assert\NotBlank(message: '接收人 openID 不能为空', groups: ['miniProgram'])]
    public string $openId = '';
}
