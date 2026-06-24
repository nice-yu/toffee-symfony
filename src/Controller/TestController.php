<?php
declare(strict_types=1);

namespace App\Controller;

use App\Attribute\ValidatorGroup;
use App\Cache\TestCacheDto;
use App\Dto\Event\Test\TestEventDto;
use App\Dto\Request\Common\NoticeRequestDto;
use App\Dto\Response\Common\ArrayResponseDto;
use App\Enum\Common\StateEnum;
use App\Event\TestEvent;
use App\Exceptions\ServiceException;
use App\Message\NoticeMessage;
use App\Message\TestMessage;
use App\Utils\RedisUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * 框架测试控制器
 * @uses TestController
 *
 * 注意：本控制器为开发阶段测试用途，直接调用 Enum、返回 JsonResponse。
 * 正式业务代码中 Controller 只能调用 Action，必须通过 ResponseDto 输出响应。
 */
#[Route('/test')]
class TestController
{
    /**
     * 测试 StateEnum 各项方法
     *
     * 注意：正式环境不允许 Controller 直接调用 Enum，此处仅为功能验证。
     */
    #[Route('/enum')]
    public function enum(): Response
    {
        $data = [
            'zh'    => StateEnum::ENABLED->zh(),
            'en'    => StateEnum::ENABLED->en(),
            'meta'  => StateEnum::ENABLED->meta(),
            'list'  => StateEnum::list(),
            'group' => StateEnum::group('zh', 'default'),
        ];

        return new JsonResponse($data);
    }

    /**
     * 测试 DTO 注入：自动反序列化 + 校验，直接访问属性
     *
     * 注意：正式环境不允许 Controller 直接返回 JsonResponse，此处仅为功能验证，
     * 展示 RequestDtoResolver 自动注入和 ValidatorGroup 校验分组功能。
     */
    #[Route('/dto')]
    #[ValidatorGroup(['miniProgram'])]
    public function transformer(NoticeRequestDto $dto): Response
    {
        return new JsonResponse([
            'tempId' => $dto->tempId,
            'openId' => $dto->openId,
        ]);
    }

    /**
     * 测试 ArrayResponseDto：DI 注入后赋值，原生数组输出
     */
    #[Route('/array')]
    public function arr(ArrayResponseDto $response): Response
    {
        $response->data = [1, 2, 3];
        return $response->response();
    }

    /**
     * 测试 RedisUtils 缓存读写：CacheDto → json 序列化 → Redis → 反射反序列化 → CacheDto
     *
     * 注意：正式环境不允许 Controller 直接操作 Utils，此处仅为功能验证。
     */
    #[Route('/cache')]
    public function cache(RedisUtils $redis): JsonResponse
    {
        $dto = new TestCacheDto(1001);
        $dto->nickname = '测试用户';
        $dto->age       = 25;
        $dto->role      = 'admin';

        $setResult = $redis->setCache($dto);

        $getResult = $redis->getCache(TestCacheDto::class, 1001);

        $delResult = $redis->delCache($dto);

        return new JsonResponse([
            'set'   => $setResult,
            'get'   => [
                'nickname' => $getResult?->nickname,
                'age'      => $getResult?->age,
                'role'     => $getResult?->role,
            ],
            'del'   => $delResult,
        ]);
    }

    /**
     * 测试事件委托：创建 EventDto → 封装 Event → EventDispatcher 分发 → Subscriber 监听处理
     *
     * 注意：正式环境 Controller 不能直接操作 EventDispatcher，此处仅为功能验证。
     */
    #[Route('/event')]
    public function event(EventDispatcherInterface $dispatcher): JsonResponse
    {
        $dto   = new TestEventDto('测试事件', rand(1, 100));
        $event = new TestEvent($dto);
        $dispatcher->dispatch($event);

        return new JsonResponse([
            'dispatched' => true,
            'name'       => $dto->name,
            'score'      => $dto->score,
        ]);
    }

    /**
     * 测试 Messenger：同步投递消息 → Handler 处理 → 返回投递结果
     *
     * 注意：正式环境 Controller 不能直接操作 MessageBus，此处仅为功能验证。
     */
    #[Route('/message')]
    public function message(MessageBusInterface $bus): JsonResponse
    {
        $testCount   = 0;
        $noticeCount = 0;

        for ($i = 0; $i < 99; $i++) {
            try {
                // 穿插投递两种消息
                if ($i % 2 === 0) {
                    $message = new TestMessage('Hello Messenger', rand(1000, 9999));
                    $bus->dispatch($message);
                    $testCount++;
                } else {
                    $message = new NoticeMessage(
                        title: "通知 {$i}",
                        body:  "内容 " . rand(1000, 9999),
                    );
                    $bus->dispatch($message);
                    $noticeCount++;
                }
            } catch (ExceptionInterface $e) {
                throw new ServiceException(message: $e->getMessage());
            }
        }

        return new JsonResponse([
            'dispatched'  => true,
            'testTotal'   => $testCount,
            'noticeTotal' => $noticeCount,
        ]);
    }
}
