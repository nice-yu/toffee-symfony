<?php
declare(strict_types=1);

namespace App\EventSubscriber;

use App\Enum\Common\DeviceTypeEnum;
use App\Exceptions\ServiceException;
use App\Exceptions\ValidatorParamsException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * 请求签名验证订阅器
 * @uses SignatureSubscriber
 *
 * 在所有请求到达 Controller 前校验接口签名，验证失败则阻断请求。
 */
class SignatureSubscriber implements EventSubscriberInterface
{
    /**
     * 默认签名有效期（秒）
     */
    private const DEFAULT_SIGN_EXPIRE = 60;

    // 免签名路由列表（如健康检查、调试端点）
    /** @var string[] */
    private array $directRouter = [];

    public function __construct(
        private ?LoggerInterface $logger = null,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onVerifySignature', 32],
        ];
    }

    /**
     * 验证请求签名
     * @uses onVerifySignature
     */
    public function onVerifySignature(RequestEvent $event): void
    {
        // 本地环境跳过签名验证
        if (($_SERVER['APP_ENV_CONFIG'] ?? '') === 'local') {
            return;
        }

        $request = $event->getRequest();

        // 查看当前接口是否需要走签名
        if (in_array($request->getRequestUri(), $this->directRouter, true)) {
            return;
        }

        // 获取签名信息
        $rawType    = $request->headers->get('Device-Type');
        $deviceType = is_numeric($rawType) ? (int) $rawType : null;
        $timer      = (int) ($request->headers->get('Timer') ?? 0);

        // 获取到当前发起请求设备使用类型
        if (is_null($deviceType)) {
            $this->logger?->warning('签名验证失败 - 设备类型缺失', [
                'client_ip' => $request->getClientIp(),
            ]);
            throw new ValidatorParamsException(message: '请求参数不完整，请检查设备类型');
        }

        /** 设备类型必须为特定类型 */
        $deviceEnum = DeviceTypeEnum::list()[$deviceType] ?? null;
        if (is_null($deviceEnum)) {
            $this->logger?->warning('签名验证失败 - 无效设备类型', [
                'device_type' => $deviceType,
                'client_ip'   => $request->getClientIp(),
            ]);
            throw new ValidatorParamsException(message: '设备类型无效');
        }

        /** 获取设备对应密钥 */
        $secret   = $_SERVER['SIGN_' . strtoupper($deviceEnum['en'])] ?? null;
        $signName = 'SIGN_' . strtoupper($deviceEnum['en']);
        if (is_null($secret)) {
            throw new ServiceException(message: "签名密钥丢失: {$signName}");
        }

        /** 查看本次签名是否在有效期内（包括未来时间戳防重放） */
        $expire = (int) ($_SERVER['SIGN_EXPIRE'] ?? self::DEFAULT_SIGN_EXPIRE);
        $times  = time();
        $diff   = abs($times - $timer);

        if ($diff > $expire) {
            $this->logger?->warning('签名验证失败 - 签名已过期', [
                'diff'        => $diff,
                'expire'      => $expire,
                'client_ip'   => $request->getClientIp(),
            ]);
            throw new ValidatorParamsException(errorCode: $diff, message: '签名已过期，请重新获取');
        }

        // 检测时间戳是否来自未来（防止时间篡改）
        if ($timer > $times) {
            $this->logger?->warning('签名验证失败 - 未来时间戳', [
                'timer'     => $timer,
                'current'   => $times,
                'client_ip' => $request->getClientIp(),
            ]);
            throw new ValidatorParamsException(errorCode: $diff, message: '签名时间无效');
        }

        /** 接口签名 */
        $sign  = $request->headers->get('Sign');
        $nonce = $request->headers->get('Nonce');
        $code  = hash('sha256', "Device={$deviceType}&Nonce={$nonce}&Secret={$secret}&Timer={$timer}");
        if ($sign !== $code) {
            throw new ValidatorParamsException(message: '签名信息错误');
        }
    }
}
