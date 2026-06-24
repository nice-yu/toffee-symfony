<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserProfileRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * 用户扩展信息表
 *
 * 与 users 一对一，存放昵称、头像、实名等信息
 */
#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\Table(name: 'user_profile', options: ['comment' => '用户 - 扩展信息表'])]
#[ORM\Index(name: 'idx_user_profile_user_id', columns: ['user_id'])]
class UserProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键 ID'])]
    private ?int $id = null;

    /** 关联 users.id，一对一，唯一约束 */
    #[ORM\Column(type: Types::INTEGER, unique: true, options: ['default' => 0, 'comment' => '关联 users.id，一对一'])]
    private int $userId = 0;

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => '', 'comment' => '昵称'])]
    private string $nickname = '';

    #[ORM\Column(type: Types::STRING, length: 500, options: ['default' => '', 'comment' => '头像 URL'])]
    private string $avatarUrl = '';

    #[ORM\Column(type: Types::STRING, length: 50, options: ['default' => '', 'comment' => '真实姓名'])]
    private string $realName = '';

    /** 身份证号码，唯一约束防止重复实名 */
    #[ORM\Column(type: Types::STRING, length: 18, unique: true, nullable: true, options: ['comment' => '身份证号码'])]
    private ?string $idCard = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 0, 'comment' => '性别：0=未知 1=男 2=女'])]
    private int $gender = 0;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true, options: ['comment' => '生日'])]
    private ?DateTimeInterface $birthday = null;

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

    public function getRealName(): string 
    {
        return $this->realName;
    }

    public function setRealName(string $realName): void 
    {
        $this->realName = $realName;
    }

    public function getIdCard(): ?string 
    {
        return $this->idCard;
    }

    public function setIdCard(?string $idCard): void 
    {
        $this->idCard = $idCard;
    }

    public function getGender(): int 
    {
        return $this->gender;
    }

    public function setGender(int $gender): void 
    {
        $this->gender = $gender;
    }

    public function getBirthday(): ?DateTimeInterface 
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday): void 
    {
        $this->birthday = $birthday;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): void 
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface 
    {
        return $this->updatedAt;
    }
}
