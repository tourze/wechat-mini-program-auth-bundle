<?php

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;

class CodeSessionLogRepositoryTest extends TestCase
{
    public function testRepositoryEntityClass(): void
    {
        $reflectionClass = new \ReflectionClass(CodeSessionLogRepository::class);
        $constructor = $reflectionClass->getConstructor();
        
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());
        self::assertEquals('registry', $constructor->getParameters()[0]->getName());
    }

    public function testRepositoryExtendsServiceEntityRepository(): void
    {
        $reflectionClass = new \ReflectionClass(CodeSessionLogRepository::class);
        self::assertEquals('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', $reflectionClass->getParentClass()->getName());
    }

    public function testRepositoryTargetEntity(): void
    {
        self::assertEquals(CodeSessionLog::class, 'WechatMiniProgramAuthBundle\Entity\CodeSessionLog');
    }
}