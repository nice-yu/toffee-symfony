<?php
declare(strict_types=1);

namespace App\Dto\Response\Common;

use App\Dto\Transformer\AbstractResponseDto;

class BufferResponseDto extends AbstractResponseDto
{
    public string $buffer = '';

    /**
     * 设置响应数据
     * @param string $base64 图片 buffer
     */
    public function trans(string $base64): self
    {
        $this->buffer = "data:image/png;base64,$base64";
        return $this;
    }

    protected function result(): string
    {
        return $this->buffer;
    }
}
