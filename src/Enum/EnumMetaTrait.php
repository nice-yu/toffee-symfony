<?php
/** @noinspection PhpUndefinedClassInspection */
declare(strict_types=1);

namespace App\Enum;

use App\Attribute\EnumMeta;
use ReflectionEnum;

/**
 * 枚举元数据 Trait
 *
 * --- Trait 提供 ---
 * - $case->zh()           获取中文标签
 * - $case->en()           获取英文标签
 * - $case->meta()         获取完整 EnumMeta 对象（实例方法）
 * - FooEnum::local(1)     按值获取 EnumMeta 对象（静态方法）
 * - FooEnum::list()       获取全部案例 [value => [zh, en, value]]
 * - FooEnum::group('zh', 'groupName')  按分组收集，key=案例值
 *
 * --- PHP 8.1 原生枚举自带 ---
 * - FooEnum::cases()        全部案例列表
 * - FooEnum::from(1)        按值获取案例（无匹配抛异常）
 * - FooEnum::tryFrom(1)     按值获取案例（无匹配返回 null）
 * - $case->value            案例的 backing 值
 * - $case->name             案例名称
 *
 * 用法示例：
 * <pre>
 * StateEnum::ENABLED->zh();          // '启用'
 * StateEnum::ENABLED->en();          // 'enabled'
 * StateEnum::ENABLED->value;         // 1
 * StateEnum::list();                 // [1 => ['zh'=>'启用', 'en'=>'enabled', 'value'=>1], ...]
 * StateEnum::group('zh', 'default'); // [1 => '启用', 2 => '禁用']
 * </pre>
 */
trait EnumMetaTrait
{
    /**
     * 反射读取枚举案例的 EnumMeta 属性（静态缓存，单例仅反射一次）
     * @noinspection PhpUndefinedFieldInspection
     */
    private static function reflectMeta(self $case): ?EnumMeta
    {
        static $cache = [];

        $key = static::class . '::' . $case->name;
        if (!isset($cache[$key])) {
            $ref   = new ReflectionEnum(static::class);
            $attrs = $ref->getCase($case->name)->getAttributes(EnumMeta::class);
            $cache[$key] = !empty($attrs) ? $attrs[0]->newInstance() : null;
        }

        return $cache[$key];
    }

    /**
     * 获取完整的 EnumMeta 对象（实例方法）
     */
    public function meta(): ?EnumMeta
    {
        return self::reflectMeta($this);
    }

    /**
     * 按值获取 EnumMeta 对象（静态方法）
     *
     * @param int $value 枚举 backing 值
     * @return EnumMeta|null
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function local(int $value): ?EnumMeta
    {
        $case = static::tryFrom($value);

        return $case?->meta();
    }

    /**
     * 获取中文标签
     */
    public function zh(): ?string
    {
        return $this->meta()?->zh;
    }

    /**
     * 获取英文标签
     */
    public function en(): ?string
    {
        return $this->meta()?->en;
    }

    /**
     * 获取全部枚举案例
     *
     * @return array<int, array{zh: ?string, en: ?string, value: int}>
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function list(): array
    {
        $result = [];
        foreach (static::cases() as $case) {
            $meta = $case->meta();
            $result[$case->value] = [
                'zh'    => $meta?->zh,
                'en'    => $meta?->en,
                'value' => $case->value,
            ];
        }
        return $result;
    }

    /**
     * 按分组标签收集枚举值，key 为案例值
     *
     * @param string $type          取值类型 zh / en / value
     * @param string $targetGroup   目标分组名
     * @param bool   $includeDefault 是否包含 default 分组（默认包含）
     * @noinspection PhpUndefinedMethodInspection
     */
    public static function group(string $type, string $targetGroup, bool $includeDefault = true): array
    {
        $result = [];
        foreach (static::cases() as $case) {
            $meta = $case->meta();

            $match = ($includeDefault
                && in_array('default', $meta?->groups ?? [], true))
                || in_array($targetGroup, $meta?->groups ?? [], true);

            if ($match) {
                $result[$case->value] = match ($type) {
                    'zh' => $meta?->zh,
                    'en' => $meta?->en,
                    default => $case->value,
                };
            }
        }
        return $result;
    }
}
