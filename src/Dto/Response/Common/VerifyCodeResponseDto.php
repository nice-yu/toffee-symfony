<?php
declare(strict_types=1);

namespace App\Dto\Response\Common;

use App\Dto\Transformer\AbstractResponseDto;

/**
 * 验证码响应 DTO - 支持图形验证码、短信验证码等
 * @uses VerifyCodeResponseDto
 */
class VerifyCodeResponseDto extends AbstractResponseDto
{
    public string $key  = '';

    /** captcha: 图形验证码, sms: 短信验证码 */
    public string $type = '';

    /** 短信验证码的内容 */
    public string $message = '';

    public function trans(array $data): self
    {
        $this->key      = $data['key'] ?? '';
        $this->type     = $data['type'] ?? 'captcha';
        $this->message  = ($this->type === 'captcha') ? $data['base64'] : $data['message'] ;
        return $this;
    }
}
