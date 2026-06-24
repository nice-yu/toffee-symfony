<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserWalletLogRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户钱包日志表
 *
 * 记录每一次余额变动的完整链路：变动前 → 变动金额 → 变动后
 */
#[ORM\Entity(repositoryClass: UserWalletLogRepository::class)]
#[ORM\Table(name: 'user_wallet_log', options: ['comment' => '用户 - 钱包日志表'])]
#[ORM\Index(name: 'idx_wallet_log_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_wallet_log_source_id', columns: ['source_id'])]
#[ORM\Index(name: 'idx_wallet_log_order_id', columns: ['order_id'])]
#[ORM\Index(name: 'idx_wallet_log_created_at', columns: ['created_at'])]
class UserWalletLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    /** 关联 users.id */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '关联 users.id'])]
    private int $userId = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '变动前余额'])]
    private string $balanceBefore = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '变动金额'])]
    private string $changeAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00', 'comment' => '变动后余额'])]
    private string $balanceAfter = '0.00';

    /** 操作类型：1=增加 2=减少 */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '操作类型：1=增加 2=减少'])]
    private int $type = 1;

    /** 业务类型：1=佣金结算 2=余额提现 3=提现失败退回 */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '业务类型：1=佣金结算 2=余额提现 3=提现失败退回'])]
    private int $orderType = 1;

    /** 钱包类型，见 WalletEnum */
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0, 'comment' => '钱包类型（WalletEnum 枚举）'])]
    private int $walletType = 0;

    /** 来源幂等键（如订单号、提现单号） */
    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => '', 'comment' => '来源幂等键（如订单号、提现单号）'])]
    private string $sourceId = '';

    /** 来源业务类型：1=佣金 2=提现 3=退款 */
    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => '', 'comment' => '来源业务类型：1=佣金 2=提现 3=退款'])]
    private string $sourceType = '';

    /** 关联订单 ID（order.id，非订单号） */
    #[ORM\Column(name: 'order_id', type: Types::INTEGER, options: ['default' => 0, 'comment' => '关联订单 ID（order.id，非订单号）'])]
    private int $orderId = 0;

    #[ORM\Column(type: Types::STRING, length: 191, options: ['default' => '', 'comment' => '备注'])]
    private string $remark = '';

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $createdAt = null;

    /** 结算时间，手动赋值 */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '结算时间'])]
    private ?DateTimeInterface $settleAt = null;

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

    public function getBalanceBefore(): string
    {
        return $this->balanceBefore;
    }

    public function setBalanceBefore(string $balanceBefore): void
    {
        $this->balanceBefore = $balanceBefore;
    }

    public function getChangeAmount(): string
    {
        return $this->changeAmount;
    }

    public function setChangeAmount(string $changeAmount): void
    {
        $this->changeAmount = $changeAmount;
    }

    public function getBalanceAfter(): string
    {
        return $this->balanceAfter;
    }

    public function setBalanceAfter(string $balanceAfter): void
    {
        $this->balanceAfter = $balanceAfter;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getOrderType(): int
    {
        return $this->orderType;
    }

    public function setOrderType(int $orderType): void
    {
        $this->orderType = $orderType;
    }

    public function getWalletType(): int
    {
        return $this->walletType;
    }

    public function setWalletType(int $walletType): void
    {
        $this->walletType = $walletType;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function setSourceId(string $sourceId): void
    {
        $this->sourceId = $sourceId;
    }

    public function getSourceType(): string
    {
        return $this->sourceType;
    }

    public function setSourceType(string $sourceType): void
    {
        $this->sourceType = $sourceType;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getSettleAt(): ?DateTimeInterface
    {
        return $this->settleAt;
    }

    public function setSettleAt(?DateTimeInterface $settleAt): void
    {
        $this->settleAt = $settleAt;
    }
}
