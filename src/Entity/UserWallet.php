<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserWalletRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户钱包表
 *
 * 与 users 一对一，记录余额、冻结金额、各平台累计收益
 */
#[ORM\Entity(repositoryClass: UserWalletRepository::class)]
#[ORM\Table(name: 'user_wallet', options: ['comment' => '用户 - 钱包表'])]
#[ORM\Index(name: 'idx_user_wallet_user_id', columns: ['user_id'])]
class UserWallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    /** 关联 users.id，一对一，唯一约束 */
    #[ORM\Column(type: Types::INTEGER, unique: true, options: ['default' => 0, 'comment' => '关联 users.id，一对一'])]
    private int $userId = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '账户余额'])]
    private string $balance = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '冻结余额'])]
    private string $frozen = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '累计总收益'])]
    private string $totalEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '淘宝累计总收益'])]
    private string $taobaoEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '京东累计总收益'])]
    private string $jdEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '拼多多累计总收益'])]
    private string $pddEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '唯品会累计总收益'])]
    private string $vipEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '美团外卖累计总收益'])]
    private string $mtEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '淘宝闪购累计总收益'])]
    private string $flashEarnings = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '抖音累计总收益'])]
    private string $dyEarnings = '0.00';

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

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): void
    {
        $this->balance = $balance;
    }

    public function getFrozen(): string
    {
        return $this->frozen;
    }

    public function setFrozen(string $frozen): void
    {
        $this->frozen = $frozen;
    }

    public function getTotalEarnings(): string
    {
        return $this->totalEarnings;
    }

    public function setTotalEarnings(string $totalEarnings): void
    {
        $this->totalEarnings = $totalEarnings;
    }

    public function getTaobaoEarnings(): string
    {
        return $this->taobaoEarnings;
    }

    public function setTaobaoEarnings(string $taobaoEarnings): void
    {
        $this->taobaoEarnings = $taobaoEarnings;
    }

    public function getJdEarnings(): string
    {
        return $this->jdEarnings;
    }

    public function setJdEarnings(string $jdEarnings): void
    {
        $this->jdEarnings = $jdEarnings;
    }

    public function getPddEarnings(): string
    {
        return $this->pddEarnings;
    }

    public function setPddEarnings(string $pddEarnings): void
    {
        $this->pddEarnings = $pddEarnings;
    }

    public function getVipEarnings(): string
    {
        return $this->vipEarnings;
    }

    public function setVipEarnings(string $vipEarnings): void
    {
        $this->vipEarnings = $vipEarnings;
    }

    public function getMtEarnings(): string
    {
        return $this->mtEarnings;
    }

    public function setMtEarnings(string $mtEarnings): void
    {
        $this->mtEarnings = $mtEarnings;
    }

    public function getFlashEarnings(): string
    {
        return $this->flashEarnings;
    }

    public function setFlashEarnings(string $flashEarnings): void
    {
        $this->flashEarnings = $flashEarnings;
    }

    public function getDyEarnings(): string
    {
        return $this->dyEarnings;
    }

    public function setDyEarnings(string $dyEarnings): void
    {
        $this->dyEarnings = $dyEarnings;
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
