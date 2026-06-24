<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserDeviceRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户设备表
 *
 * 记录用户登录过的设备信息，每次登录更新设备信息和登录时间
 */
#[ORM\Entity(repositoryClass: UserDeviceRepository::class)]
#[ORM\Table(name: 'user_device', options: ['comment' => '用户 - 设备表'])]
#[ORM\Index(name: 'idx_user_device_user_id', columns: ['user_id'])]
#[ORM\UniqueConstraint(name: 'uniq_user_device_user_device', columns: ['user_id', 'device_id'])]
class UserDevice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    /** 关联 users.id */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '关联 users.id'])]
    private int $userId = 0;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 4, 'comment' => '设备类型：1=iOS 2=Android 3=Web 4=小程序'])]
    private int $deviceType = 4;

    /** 设备唯一标识，与 user_id 联合唯一，同一设备不重复记录 */
    #[ORM\Column(type: Types::STRING, length: 100, nullable: true, options: ['comment' => '设备唯一标识'])]
    private ?string $deviceId = null;

    #[ORM\Column(type: Types::STRING, length: 100, options: ['default' => '', 'comment' => '设备名称，如 iPhone 15 Pro'])]
    private string $deviceName = '';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => '', 'comment' => '操作系统版本'])]
    private string $osVersion = '';

    #[ORM\Column(type: Types::STRING, length: 20, options: ['default' => '', 'comment' => 'App 版本号'])]
    private string $appVersion = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTimeInterface $updatedAt = null;

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

    public function getDeviceType(): int 
    {
        return $this->deviceType;
    }

    public function setDeviceType(int $deviceType): void 
    {
        $this->deviceType = $deviceType;
    }

    public function getDeviceId(): ?string 
    {
        return $this->deviceId;
    }

    public function setDeviceId(?string $deviceId): void 
    {
        $this->deviceId = $deviceId;
    }

    public function getDeviceName(): string 
    {
        return $this->deviceName;
    }

    public function setDeviceName(string $deviceName): void 
    {
        $this->deviceName = $deviceName;
    }

    public function getOsVersion(): string 
    {
        return $this->osVersion;
    }

    public function setOsVersion(string $osVersion): void 
    {
        $this->osVersion = $osVersion;
    }

    public function getAppVersion(): string 
    {
        return $this->appVersion;
    }

    public function setAppVersion(string $appVersion): void 
    {
        $this->appVersion = $appVersion;
    }

    public function getCreatedAt(): ?DateTimeInterface 
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface 
    {
        return $this->updatedAt;
    }
}
