<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户主表
 *
 * 登录方式：手机号+密码 / 微信 openid（通过 user_wechat JOIN）
 * 密码哈希 + 盐值存储，salt 每次随机生成
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users', options: ['comment' => '用户 - 登录主表'])]
#[ORM\Index(name: 'idx_users_parent_id', columns: ['parent_id'])]
#[ORM\Index(name: 'idx_users_status', columns: ['status'])]
#[ORM\Index(name: 'idx_users_phone_status', columns: ['phone', 'status'])]
class Users
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '用户 ID，自增主键'])]
    private ?int $id = null;

    /** 手机号，唯一约束防止重复注册。可为空（纯微信登录用户无手机号） */
    #[ORM\Column(type: Types::STRING, length: 20, unique: true, nullable: true, options: ['comment' => '手机号，密码登录用，可为空（纯微信登录用户无手机号）'])]
    private ?string $phone = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['comment' => '密码哈希，可为空（纯微信登录用户无密码）'])]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 64, options: ['default' => '', 'comment' => '密码盐，每次生成新盐'])]
    private string $salt = '';

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '用户等级'])]
    private int $level = 1;

    #[ORM\Column(type: Types::STRING, length: 6, unique: true, nullable: true, options: ['comment' => '邀请码'])]
    private ?string $inviteCode = null;

    /** 邀请人 ID，自关联 users.id。0 表示无邀请人 */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '邀请人 ID，自关联 users.id。0 表示无邀请人'])]
    private int $parentId = 0;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '用户状态：0=禁用 1=正常 2=封禁'])]
    private int $status = 1;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    #[Gedmo\Timestampable(on: 'update')]
    private ?DateTimeInterface $updatedAt = null;

    /** 关联数据 — 非映射字段，JOIN 查询结果或注册时附带 */
    private ?UserProfile $profile = null;
    private ?UserWallet  $wallet  = null;
    private ?UserDevice  $device  = null;

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): void
    {
        $this->profile = $profile;
    }

    public function getWallet(): ?UserWallet
    {
        return $this->wallet;
    }

    public function setWallet(?UserWallet $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function getDevice(): ?UserDevice
    {
        return $this->device;
    }

    public function setDevice(?UserDevice $device): void
    {
        $this->device = $device;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getInviteCode(): ?string
    {
        return $this->inviteCode;
    }

    public function setInviteCode(?string $inviteCode): void
    {
        $this->inviteCode = $inviteCode;
    }

    public function getParentId(): int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
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
