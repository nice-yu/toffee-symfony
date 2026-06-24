<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserWalletLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 用户钱包日志数据仓库
 */
class UserWalletLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWalletLog::class);
    }
}
