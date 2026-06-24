<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserWallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 用户钱包数据仓库
 */
class UserWalletRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWallet::class);
    }
}
