<?php

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;

class PhoneNumberRepositoryTest extends TestCase
{
    public function testRepositoryEntityClass(): void
    {
        $reflectionClass = new \ReflectionClass(PhoneNumberRepository::class);
        $constructor = $reflectionClass->getConstructor();
        
        self::assertNotNull($constructor);
        self::assertCount(1, $constructor->getParameters());
        self::assertEquals('registry', $constructor->getParameters()[0]->getName());
    }

    public function testRepositoryExtendsServiceEntityRepository(): void
    {
        $reflectionClass = new \ReflectionClass(PhoneNumberRepository::class);
        self::assertEquals('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', $reflectionClass->getParentClass()->getName());
    }

    public function testRepositoryTargetEntity(): void
    {
        self::assertEquals(PhoneNumber::class, 'WechatMiniProgramAuthBundle\Entity\PhoneNumber');
    }
}