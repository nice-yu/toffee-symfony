<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\UserDevice;
use App\Entity\UserProfile;
use App\Entity\Users;
use App\Entity\UserWallet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * 用户数据仓库
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * 获取用户信息
     * TODO:: [id, phone, unionid, openid, inviteCode]
     * @param int $id
     * @param array $with
     * @return Users|null
     */
    public function findUserWithRelations(int $id, array $with = []): ?Users
    {
        $result = $this->findByUserWithRelations([$id], $with);

        /** 获取到数据信息 */
        if (count($result) > 0) {
            /** 整理数据信息 */
            $result = $this->organize($result);

            /** 只返回一条数据 */
            return array_shift($result);
        }

        return null;
    }

    /**
     * 获取到用户完整信息
     * @param array $ids
     * @param array $args
     * @return array
     */
    public function findByUserWithRelations(array $ids, array $args = ['up', 'uw', 'ud']): array
    {
        if (empty($ids)) {
            return [];
        }

        /** 分批查询避免ID过多 */
        $allUsers = [];
        $batches = array_chunk($ids, 100);

        foreach ($batches as $batchIds) {
            /** 查询加载关联数据 */
            $query = $this->createQueryBuilder('u');
            if (count($args) > 0) {
                $query->addSelect($args);
            }
            if (in_array('up', $args, true)) {
                $query->leftJoin(UserProfile::class, 'up', 'WITH', 'up.userId = u.id');
            }

            if (in_array('uw', $args, true)) {
                $query->leftJoin(UserWallet::class, 'uw', 'WITH', 'uw.userId = u.id');
            }

            if (in_array('ud', $args, true)) {
                $query->leftJoin(UserDevice::class, 'ud', 'WITH', 'ud.userId = u.id');
            }

            $result = $query->where('u.id IN (:ids)')
                ->setParameter('ids', $batchIds)
                ->getQuery()
                ->getResult();

            /** 整理数据信息 */
            if (is_array($result) && count($result) > 0) {
                $organized = $this->organize($result);
                $allUsers = array_merge($allUsers, array_values($organized));
            }
        }

        return $allUsers;
    }

    /**
     * 数据整理
     * @param array $data
     * @return array
     */
    public function organize(array $data): array
    {
        /** 主表数据信息 */
        $listUser = [];
        foreach ($data as $item) {
            if ($item instanceof Users) {
                $listUser[$item->getId()] = $item;
            }
        }

        /** 写入附表数据信息 */
        foreach ($data as $item) {
            if ($item instanceof UserProfile && isset($listUser[$item->getUserId()])) {

                /** 用户信息 */
                $listUser[$item->getUserId()]->setProfile($item);
            } else if ($item instanceof UserWallet && isset($listUser[$item->getUserId()])) {

                /** 用户钱包 */
                $listUser[$item->getUserId()]->setWallet($item);
            } else if ($item instanceof UserDevice && isset($listUser[$item->getUserId()])) {

                /** 用户设备信息 */
                $listUser[$item->getUserId()]->setDevice($item);
            }
        }
        return $listUser;
    }

}
