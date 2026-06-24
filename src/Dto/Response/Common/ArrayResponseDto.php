<?php
declare(strict_types=1);

namespace App\Dto\Response\Common;

use App\Dto\Transformer\AbstractResponseDto;

class ArrayResponseDto extends AbstractResponseDto
{
    public function __construct(
        public array $data = []
    ) {}

    protected function result(): array
    {
        return $this->data;
    }
}
