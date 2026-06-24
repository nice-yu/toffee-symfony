<?php
declare(strict_types=1);

namespace App\Utils\Token;

use App\Utils\RsaUtils;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Token 工具类
 */
class TokenUtils
{

    private int $id;
    private int $deviceType;
    private int $expire;

    public function __construct(
        private RsaUtils        $rsaUtils,
        private LoggerInterface $logger,
    ) {}

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setDeviceType(int $deviceType): self
    {
        $this->deviceType = $deviceType;
        return $this;
    }

    public function setExpire(int $expire): self
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * 生成 Token
     * @return string
     */
    public function generate(): string
    {
        $dto = new TokenDto();
        $dto->id     = $this->id;
        $dto->device = $this->deviceType;
        $dto->expire = $this->expire;

        return $this->rsaUtils->encrypt(json_encode($dto, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 校验并解析 Token
     *
     * @param string $token
     * @return TokenDto|null 过期或解密失败返回 null
     */
    public function verify(string $token): ?TokenDto
    {
        try {
            $data = $this->rsaUtils->decrypt($token);
            if ($data === null) {
                return null;
            }

            $obj = json_decode($data);
            if (!$obj || !isset($obj->id, $obj->expire, $obj->device)) {
                return null;
            }

            $dto = new TokenDto();
            $dto->id     = (int)$obj->id;
            $dto->device = (int)$obj->device;
            $dto->expire = (int)$obj->expire;

            if ($dto->expire < time()) {
                return null;
            }

            return $dto;
        } catch (Exception $e) {
            $this->logger->error('Token 校验失败', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
