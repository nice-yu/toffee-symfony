<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserLoginLogRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户登录日志表
 *
 * 记录每次登录的成败、方式、设备、IP 等信息，用于安全审计
 */
#[ORM\Entity(repositoryClass: UserLoginLogRepository::class)]
#[ORM\Table(name: 'user_login_log', options: ['comment' => '用户 - 登录日志表'])]
#[ORM\Index(name: 'idx_user_login_log_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_login_log_created_at', columns: ['created_at'])]
class UserLoginLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '日志 ID，大表用 bigint'])]
    private ?int $id = null;

    /** 关联 users.id */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '关联 users.id'])]
    private int $userId = 0;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 2, 'comment' => '登录方式：1=手机号验证码, 2=手机密码, 3=微信 openid'])]
    private int $loginType = 2;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 4, 'comment' => '设备类型：1=iOS, 2=Android, 3=Web 4=小程序'])]
    private int $deviceType = 4;

    #[ORM\Column(type: Types::STRING, length: 45, options: ['default' => '', 'comment' => '登录 IP'])]
    private string $ipAddress = '';

    #[ORM\Column(type: Types::STRING, length: 300, options: ['default' => '', 'comment' => 'User-Agent'])]
    private string $userAgent = '';

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '登录结果：0=失败, 1=成功'])]
    private int $loginResult = 1;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['default' => '', 'comment' => '失败原因'])]
    private string $failReason = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '登录时间'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $createdAt = null;

    public function getId(): ?int 
    {
        return $this->id;
    }

    public function setId(?int $id): void 
    {
        $this->id = $id;
    }

    public function getUserId(): int 
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void 
    {
        $this->userId = $userId;
    }

    public function getLoginType(): int 
    {
        return $this->loginType;
    }

    public function setLoginType(int $loginType): void 
    {
        $this->loginType = $loginType;
    }

    public function getDeviceType(): int 
    {
        return $this->deviceType;
    }

    public function setDeviceType(int $deviceType): void 
    {
        $this->deviceType = $deviceType;
    }

    public function getIpAddress(): string 
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): void 
    {
        $this->ipAddress = $ipAddress;
    }

    public function getUserAgent(): string 
    {
        return $this->userAgent;
    }

    public function setUserAgent(string $userAgent): void 
    {
        $this->userAgent = $userAgent;
    }

    public function getLoginResult(): int 
    {
        return $this->loginResult;
    }

    public function setLoginResult(int $loginResult): void 
    {
        $this->loginResult = $loginResult;
    }

    public function getFailReason(): string 
    {
        return $this->failReason;
    }

    public function setFailReason(string $failReason): void 
    {
        $this->failReason = $failReason;
    }

    public function getCreatedAt(): ?DateTimeInterface 
    {
        return $this->createdAt;
    }
}
