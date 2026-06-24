<?php
declare(strict_types=1);

namespace App\Dto\Transformer;

use App\Contract\ResponseDtoInterface;
use Closure;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Response DTO 基类
 *
 * 子类可覆写 result() 控制 response 的 result 字段。
 */
abstract class AbstractResponseDto implements ResponseDtoInterface
{
    /**
     * 批量实体转 DTO
     *
     * @param iterable $objects 实体集合
     * @param Closure  $closure 转换回调
     * @return array
     */
    public function each(iterable $objects, Closure $closure): array
    {
        $dto = [];

        foreach ($objects as $object) {
            $dto[] = $closure($object);
        }

        return $dto;
    }

    /**
     * 构建 result 字段，默认返回对象本身
     */
    protected function result(): mixed
    {
        return $this;
    }

    /**
     * 输出 JSON Response
     *
     * @param int    $code    业务状态码
     * @param string $message 提示信息
     */
    public function response(int $code = 0, string $message = 'ok'): JsonResponse
    {
        return new JsonResponse([
            'code'    => $code,
            'message' => $message,
            'result'  => $this->result(),
        ]);
    }
}
