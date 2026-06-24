<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserWechatRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 微信绑定表
 *
 * 允许一个用户绑定多个微信，通过 user_id JOIN users
 * 微信 openid 登录时，查此表获取 user_id
 */
#[ORM\Entity(repositoryClass: UserWechatRepository::class)]
#[ORM\Table(name: 'user_wechat', options: ['comment' => '用户 - 微信绑定表'])]
#[ORM\Index(name: 'idx_user_wechat_user_id', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_wechat_unionid', columns: ['unionid'])]
#[ORM\UniqueConstraint(name: 'uniq_user_wechat_user_app', columns: ['user_id', 'app_type'])]
class UserWechat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    /** 关联 users.id，一对多 */
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0, 'comment' => '关联 users.id，一对多'])]
    private int $userId = 0;

    /** 微信 openid，全局唯一，防止同一微信重复注册。无默认值，必须赋值 */
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, options: ['comment' => '微信 openid，唯一，防止同一微信重复注册'])]
    private string $openid;

    #[ORM\Column(type: Types::STRING, length: 64, options: ['default' => '', 'comment' => '微信 unionid，同主体下唯一'])]
    private string $unionid = '';

    #[ORM\Column(type: Types::STRING, length: 100, options: ['default' => '', 'comment' => '微信昵称，每次登录同步更新'])]
    private string $nickname = '';

    #[ORM\Column(type: Types::STRING, length: 500, options: ['default' => '', 'comment' => '微信头像 URL，每次登录同步更新'])]
    private string $avatarUrl = '';

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 1, 'comment' => '应用类型：1=小程序 2=公众号 3=App'])]
    private int $appType = 1;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?DateTimeInterface $bindAt = null;

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

    public function getOpenid(): string
    {
        return $this->openid;
    }

    public function setOpenid(string $openid): void
    {
        $this->openid = $openid;
    }

    public function getUnionid(): string
    {
        return $this->unionid;
    }

    public function setUnionid(string $unionid): void
    {
        $this->unionid = $unionid;
    }

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getAvatarUrl(): string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(string $avatarUrl): void
    {
        $this->avatarUrl = $avatarUrl;
    }

    public function getAppType(): int
    {
        return $this->appType;
    }

    public function setAppType(int $appType): void
    {
        $this->appType = $appType;
    }

    public function getBindAt(): ?DateTimeInterface
    {
        return $this->bindAt;
    }
}
