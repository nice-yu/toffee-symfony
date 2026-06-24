<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserLoginLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 用户登录日志数据仓库
 */
class UserLoginLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLoginLog::class);
    }
}
