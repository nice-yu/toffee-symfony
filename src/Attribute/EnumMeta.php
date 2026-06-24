<?php
declare(strict_types=1);

namespace App\Attribute;

use Attribute;

/**
 * 枚举属性定义
 */
#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class EnumMeta
{
    /**
     * 所属分组，默认包含 'default'
     */
    public array $groups;

    /**
     * @param string   $zh     中文标签
     * @param string   $en     英文标签
     * @param string[] $groups 所属分组，自动追加 'default'
     */
    public function __construct(
        public string $zh,
        public string $en,
        array $groups = [],
    ) {
        $groups[] = 'default';
        $this->groups = array_unique($groups);
    }
}
