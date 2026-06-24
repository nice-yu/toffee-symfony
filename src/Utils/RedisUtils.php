<?php
declare(strict_types=1);

namespace App\Utils;

use App\Contract\CacheDtoInterface;
use Psr\Log\LoggerInterface;
use Redis;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use RuntimeException;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use TypeError;

/**
 * Redis 工具类
 *
 * 基于 CacheDtoInterface 统一管理缓存 Key，序列化用 json_encode/decode + 反射。
 */
class RedisUtils
{
    /** Redis 连接配置数组，包含 dsn 和 options */
    private array $redisConfig;

    /** Redis 客户端实例，懒加载单例 */
    private ?Redis $client = null;

    public function __construct(
        array   $redisConfig,
        private readonly LoggerInterface $logger,
    ) {
        $this->redisConfig = $redisConfig;
    }

    /**
     * 获取 Redis 连接（懒加载，单例）
     */
    public function getClient(): Redis
    {
        if ($this->client !== null) {
            return $this->client;
        }

        /** 从配置中提取 dsn 和 options，创建 Redis 连接 */
        ['dsn' => $dsn, 'options' => $options] = $this->redisConfig;

        return $this->client = RedisAdapter::createConnection($dsn, $options);
    }

    /**
     * 获取缓存，反序列化为 CacheDto
     *
     * @param string $className CacheDto 类名
     * @param mixed  ...$keys   缓存 Key 构造参数
     */
    public function getCache(string $className, ...$keys): ?CacheDtoInterface
    {
        /** 通过类名 + 构造参数实例化 DTO，获取缓存 key */
        $object = new $className(...$keys);
        if (!$object instanceof CacheDtoInterface) {
            return null;
        }

        /** 从 Redis 读取 JSON 数据 */
        $data = $this->getClient()->get($object->getCacheKey());
        if ($data === false) {
            return null;
        }

        /** JSON 解码 */
        $decoded = json_decode($data, true);
        if (!is_array($decoded)) {
            return null;
        }

        /** 反射填充 DTO 属性：支持任意深度嵌套对象 */
        self::populateObject($object, $decoded, self::getReflectionClass($className));

        return $object;
    }

    /**
     * 递归填充对象属性：标量直接写入，数组且目标类型为类时递归创建嵌套对象
     */
    private static function populateObject(object $object, array $data, ReflectionClass $reflection): void
    {
        foreach ($data as $key => $value) {
            if (!$reflection->hasProperty($key)) {
                continue;
            }

            $prop = $reflection->getProperty($key);

            try {
                $prop->setValue($object, $value);
            } catch (TypeError) {
                /** 类型不匹配——嵌套对象，递归填充 */
                $type = $prop->getType();
                if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && is_array($value)) {
                    $nestedClass = $type->getName();
                    if (class_exists($nestedClass)) {
                        $nested    = new $nestedClass();
                        $nestedRef = self::getReflectionClass($nestedClass);
                        self::populateObject($nested, $value, $nestedRef);
                        $prop->setValue($object, $nested);
                    }
                }
            }
        }
    }

    /**
     * 设置缓存，CacheDto 序列化为 JSON 存储，TTL 从 DTO 的 $ttl 属性读取
     */
    public function setCache(CacheDtoInterface $cache): bool
    {
        /** 缓存 key 由 DTO 的 getCacheKey() 生成 */
        $key  = $cache->getCacheKey();
        /** JSON 序列化，保留 Unicode 字符 */
        $data = json_encode($cache, JSON_UNESCAPED_UNICODE);

        /** 反射读取 DTO 的 $ttl 属性作为过期时间 */
        $reflection = self::getReflectionClass(get_class($cache));
        try {
            $prop = $reflection->getProperty('ttl');
            $prop->setAccessible(true);
            $ttl = (int) $prop->getValue($cache);
        } catch (ReflectionException $e) {
            /** DTO 缺少 $ttl 属性为异常状态，记录日志并回退为永久存储 */
            $this->logger->warning('缓存 DTO 缺少 ttl 属性，回退为永久存储', [
                'class' => get_class($cache),
                'error' => $e->getMessage(),
            ]);
            $ttl = 0;
        }

        /** 有 TTL 用 setex，无 TTL 用 set 永久存储 */
        if ($ttl > 0) {
            return $this->getClient()->setex($key, $ttl, $data);
        }

        return $this->getClient()->set($key, $data);
    }

    /**
     * 删除缓存
     */
    public function delCache(CacheDtoInterface $cache): int
    {
        return $this->getClient()->del($cache->getCacheKey());
    }

    /**
     * 获取缓存的反射类（静态缓存，避免重复反射）
     */
    private static function getReflectionClass(string $className): ReflectionClass
    {
        static $cache = [];

        if (!isset($cache[$className])) {
            try {
                $cache[$className] = new ReflectionClass($className);
            } catch (ReflectionException $e) {
                throw new RuntimeException("无法反射类: {$className}", 0, $e);
            }
        }

        return $cache[$className];
    }
}
