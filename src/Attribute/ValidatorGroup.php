<?php
declare(strict_types=1);

namespace App\Attribute;

use Attribute;

/**
 * 参数验证组
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ValidatorGroup
{
    public function __construct(
        public array $groups
    ) {}
}
