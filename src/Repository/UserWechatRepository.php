<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserWechat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 微信绑定数据仓库
 */
class UserWechatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWechat::class);
    }
}
