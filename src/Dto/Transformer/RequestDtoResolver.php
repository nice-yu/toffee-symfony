<?php
declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Attribute\ValidatorGroup;
use App\Contract\RequestDtoInterface;
use App\Exceptions\ValidatorParamsException;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeError;

/**
 * DTO 请求参数解析器
 * @uses RequestDtoResolver
 *
 * 自动将请求体反序列化为 DTO 并校验，注入 Controller 方法参数。
 *
 * 流程：
 *   请求体 → 提取参数数组 → 蛇形键名转驼峰 → 反射填充 DTO 属性 → 校验 → 注入 DTO
 *
 * 用法：在 Controller 方法参数中声明 DTO 类型即可，无需手动调用。
 */
class RequestDtoResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator,
        private ?LoggerInterface   $logger = null,
    ) {}

    /**
     * 解析 Controller 方法参数，自动注入已验证的 DTO
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** 无类型声明，跳过 */
        $type = $argument->getType();
        if ($type === null) {
            return [];
        }

        /** 排除非 DTO 参数 */
        try {
            $reflection = new ReflectionClass($type);
        } catch (ReflectionException) {
            return [];
        }

        if (!$reflection->implementsInterface(RequestDtoInterface::class)) {
            return [];
        }

        /** 提取请求参数，蛇形键名 → 驼峰键名 */
        $data  = $this->convertKeys($this->extractParams($request));

        /** 获取控制器方法上声明的校验分组 */
        $group = $this->resolveValidatorGroup($request);

        /** 反射填充 DTO 属性：遍历数组 key，找到同名属性直接 setValue */
        $dto = $this->populateDto($type, $data);

        /** 校验 */
        $errors = $group
            ? $this->validator->validate($dto, null, $group)
            : $this->validator->validate($dto);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = $error->getMessage();
            }
            throw new ValidatorParamsException(message: implode('; ', $messages));
        }

        yield $dto;
    }

    /**
     * 从 Request 提取参数数组
     * - GET       → query 参数
     * - POST JSON → 请求体 JSON 解码
     * - POST Form → request 参数
     */
    private function extractParams(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');
        $isJson      = str_contains(strtolower($contentType), 'json');

        if ($request->getMethod() === 'GET') {
            return $request->query->all();
        }

        if ($isJson) {
            $content = $request->getContent();
            if ($content === '') {
                throw new ValidatorParamsException(message: '请求体为空，期望 JSON 数据');
            }
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $error = json_last_error_msg();
                $this->logger?->error('请求体 JSON 解析失败', [
                    'error'   => $error,
                    'content' => mb_substr($content, 0, 200),
                ]);
                throw new ValidatorParamsException(message: '请求体 JSON 格式无效：' . $error);
            }
            return $data;
        }

        return $request->request->all();
    }

    /**
     * 从控制器方法读取 #[ValidatorGroup] 属性，提取校验分组
     * @noinspection PhpUndefinedMethodInspection
     */
    private function resolveValidatorGroup(Request $request): array
    {
        $controller = $request->attributes->get('_controller', '');
        if (!str_contains($controller, '::')) {
            return [];
        }

        [$class, $method] = explode('::', $controller);

        try {
            $ref = new ReflectionMethod($class, $method);
        } catch (ReflectionException) {
            return [];
        }

        $group = [];

        foreach ($ref->getAttributes() as $attribute) {
            if ($attribute->getName() === ValidatorGroup::class) {
                $group[] = $attribute->newInstance()->groups;
            }
        }

        return array_merge([], ...$group);
    }

    /**
     * 反射填充 DTO 属性
     *
     * 通过 ReflectionClass 创建 DTO 实例（绕过构造函数，保留属性默认值），
     * 然后遍历 $data 数组，将每个 key 对应的值直接写入同名属性。
     *
     * 等价于 JMS Serializer 的 deserialize(array→json, type, 'json')，
     * 但省去了中间的 json_encode/json_decode 步骤。
     */
    private function populateDto(string $class, array $data): object
    {
        try {
            $reflection = new ReflectionClass($class);
            $dto        = $reflection->newInstanceWithoutConstructor();
        } catch (ReflectionException) {
            throw new ValidatorParamsException(message: '内部错误：无法实例化 DTO');
        }

        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                try {
                    $reflection->getProperty($key)->setValue($dto, $value);
                } catch (TypeError) {
                    throw new ValidatorParamsException(message: "字段 {$key} 类型不匹配");
                }
            } else {
                $this->logger?->warning('DTO 未知字段被跳过', [
                    'class' => $class,
                    'key'   => $key,
                ]);
            }
        }

        return $dto;
    }

    /**
     * 递归转换数组键名：蛇形 → 驼峰（goods_name → goodsName）
     */
    private function convertKeys(array $data, int $depth = 0): array
    {
        if ($depth > 10) {
            return $data;
        }

        $result = [];

        foreach ($data as $key => $value) {
            $newKey = is_string($key)
                ? lcfirst(str_replace('_', '', ucwords($key, '_')))
                : $key;

            $result[$newKey] = is_array($value)
                ? $this->convertKeys($value, $depth + 1)
                : $value;
        }

        return $result;
    }
}
