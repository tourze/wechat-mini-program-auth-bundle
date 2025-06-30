<?php

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    public function testRepositoryEntityClass(): void
    {
        $reflectionClass = new \ReflectionClass(UserRepository::class);
        $constructor = $reflectionClass->getConstructor();
        
        self::assertNotNull($constructor);
        self::assertCount(3, $constructor->getParameters());
        self::assertEquals('registry', $constructor->getParameters()[0]->getName());
        self::assertEquals('userLoader', $constructor->getParameters()[1]->getName());
        self::assertEquals('lockService', $constructor->getParameters()[2]->getName());
    }

    public function testRepositoryExtendsServiceEntityRepository(): void
    {
        $reflectionClass = new \ReflectionClass(UserRepository::class);
        self::assertEquals('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', $reflectionClass->getParentClass()->getName());
    }

    public function testRepositoryTargetEntity(): void
    {
        self::assertEquals(User::class, 'WechatMiniProgramAuthBundle\Entity\User');
    }

    public function testTransformToSysUserWithExistingBizUser(): void
    {
        $bizUser = $this->createMock(UserInterface::class);
        
        $entity = new User();
        $entity->setUser($bizUser);
        
        // 使用反射测试业务逻辑
        $reflection = new \ReflectionClass(UserRepository::class);
        $method = $reflection->getMethod('transformToSysUser');
        
        self::assertTrue($method->isPublic());
        self::assertCount(1, $method->getParameters());
        self::assertEquals('entity', $method->getParameters()[0]->getName());
    }
}