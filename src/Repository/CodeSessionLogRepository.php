<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;

/**
 * @extends ServiceEntityRepository<CodeSessionLog>
 */
#[AsRepository(entityClass: CodeSessionLog::class)]
class CodeSessionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeSessionLog::class);
    }

    public function save(CodeSessionLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CodeSessionLog $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
