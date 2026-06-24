<?php
declare(strict_types=1);

namespace App\Action\Common;

use App\Cache\Common\VerifyCodeCacheDto;
use App\Exceptions\ServiceException;
use App\Utils\RedisUtils;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * 生成验证码 Action - 支持图形验证码、短信验证码等
 * @uses GenerateImgCodeAction
 */
class GenerateImgCodeAction
{
    public function __construct(
        private RedisUtils            $redisUtils,
        private LoggerInterface       $logger,
        private ParameterBagInterface $params,
    ) {}

    /**
     * 生成图形验证码，答案存入 Redis，返回 verify_key + base64 图片
     * @return array{base64: string, key: string}
     */
    public function run(): array
    {
        /** 读取图形验证码配置：从容器参数 captcha 中获取宽高、字符集、特效开关等 */
        [
            'width'         => $width,
            'height'        => $height,
            'length'        => $length,
            'charset'       => $charset,
            'distortion'    => $distortion,
            'interpolation' => $interpolation,
            'ignore'        => $ignore,
        ] = $this->params->get('captcha');

        /** 构建 Captcha 实例：PhraseBuilder 负责生成随机字符，CaptchaBuilder 负责绘制图片 */
        $builder = new PhraseBuilder($length, $charset);
        $captcha = new CaptchaBuilder(null, $builder);

        /** 画质与特效配置 */
        $captcha->setInterpolation($interpolation);
        $captcha->setDistortion($distortion);
        $captcha->setIgnoreAllEffects($ignore);

        /** 生成图片并提取验证码答案：答案统一转大写，保证后续校验不区分大小写 */
        $captcha->build($width, $height);
        $code = strtoupper($captcha->getPhrase());

        /** 生成唯一标识 key：先用 uniqid('', true) 生成随机值，再 sha256 哈希，防止碰撞 */
        $key = uniqid('', true);
        $key = bin2hex($key);
        $key = hash('md5', hash('sha256', $key));

        /** 图片转 base64 字符串，直接内联到 JSON 响应中 */
        $base64 = $captcha->inline();

        /** 验证码答案存入 Redis：一次性消费（校验后删除），TTL 由 VerifyCodeCacheDto 统一管理 */
        $cache = new VerifyCodeCacheDto($key);
        $cache->phrase  = $code;
        $cache->type    = 'captcha';

        if (!$this->redisUtils->setCache($cache)) {
            throw new ServiceException(message: 'redis 链接出现错误');
        }

        /** 记录操作日志：key 只输出前8位，不泄露完整值 */
        $this->logger->info('图形验证码生成成功', [
            'verify_key'  => substr($key, 0, 8),
            'code_length' => $length,
            'image_size'  => "{$width}x{$height}",
        ]);

        return ['key' => $key, 'base64' => $base64, 'type' => $cache->type];
    }
}
