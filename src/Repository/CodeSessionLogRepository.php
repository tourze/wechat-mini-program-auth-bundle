<?php

namespace WechatMiniProgramAuthBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;

/**
 * @method CodeSessionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeSessionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeSessionLog[]    findAll()
 * @method CodeSessionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeSessionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeSessionLog::class);
    }
}
