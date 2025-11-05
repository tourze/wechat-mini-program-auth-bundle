<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Language;
use WechatMiniProgramAuthBundle\Exception\UserRepositoryException;
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use WechatMiniProgramBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(UserRepository::class)]
#[RunTestsInSeparateProcesses]
final class UserRepositoryTest extends AbstractRepositoryTestCase
{
    private UserRepository $repository;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(UserRepository::class);
        self::assertInstanceOf(UserRepository::class, $repository);
        $this->repository = $repository;
    }

    private function createTestUser(string $openId = 'test-open-id', ?string $unionId = null): User
    {
        $user = new User();
        $user->setOpenId($openId);
        if (null !== $unionId) {
            $user->setUnionId($unionId);
        }
        $user->setNickName('Test User');
        $user->setLanguage(Language::zh_CN);

        return $user;
    }

    private function createTestMiniProgram(): MiniProgramInterface
    {
        $miniProgram = new Account();
        $miniProgram->setName('Test Mini Program');
        $miniProgram->setAppId('test-app-id-' . uniqid());
        $miniProgram->setAppSecret('test-app-secret-' . uniqid());
        $miniProgram->setValid(true);

        /** @var EntityManagerInterface $entityManager */
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($miniProgram);
        $entityManager->flush();

        return $miniProgram;
    }

    private function createTestSystemUser(): UserInterface
    {
        $user = self::getService(UserManagerInterface::class)->createUser('system-user-123');
        self::getService(UserManagerInterface::class)->saveUser($user);
        return $user;
    }

    public function testCreateUser(): void
    {
        $this->expectException(UserRepositoryException::class);
        $this->expectExceptionMessage('createUser method should not be called on Repository. Use UserService instead.');

        $miniProgram = $this->createTestMiniProgram();
        $this->repository->createUser($miniProgram, 'test-open-id');
    }

    public function testSave(): void
    {
        $user = $this->createTestUser('save-test-open-id');

        $this->repository->save($user);

        $savedUser = $this->repository->findOneBy(['openId' => 'save-test-open-id']);
        self::assertInstanceOf(User::class, $savedUser);
        self::assertEquals('save-test-open-id', $savedUser->getOpenId());
    }

    public function testSaveWithoutFlush(): void
    {
        $user = $this->createTestUser('save-no-flush-open-id');

        $this->repository->save($user, false);

        $savedUser = $this->repository->findOneBy(['openId' => 'save-no-flush-open-id']);
        self::assertNull($savedUser);
    }

    public function testRemove(): void
    {
        $user = $this->createTestUser('remove-test-open-id');
        $this->repository->save($user);

        $this->repository->remove($user);

        $removedUser = $this->repository->findOneBy(['openId' => 'remove-test-open-id']);
        self::assertNull($removedUser);
    }

    public function testLoadUserByOpenId(): void
    {
        $user = $this->createTestUser('load-by-open-id');
        $this->repository->save($user);

        $loadedUser = $this->repository->loadUserByOpenId('load-by-open-id');

        self::assertInstanceOf(User::class, $loadedUser);
        self::assertEquals('load-by-open-id', $loadedUser->getOpenId());
    }

    public function testLoadUserByOpenIdWithNonExistent(): void
    {
        $loadedUser = $this->repository->loadUserByOpenId('non-existent-open-id');

        self::assertNull($loadedUser);
    }

    public function testLoadUserByUnionId(): void
    {
        $user = $this->createTestUser('union-id-open-id', 'test-union-id');
        $this->repository->save($user);

        $loadedUser = $this->repository->loadUserByUnionId('test-union-id');

        self::assertInstanceOf(User::class, $loadedUser);
        self::assertEquals('test-union-id', $loadedUser->getUnionId());
    }

    public function testLoadUserByUnionIdWithNonExistent(): void
    {
        $loadedUser = $this->repository->loadUserByUnionId('non-existent-union-id');

        self::assertNull($loadedUser);
    }

    public function testGetBySysUserWithDirectMatch(): void
    {
        $sysUser = $this->createTestSystemUser();
        $user = $this->createTestUser('get-by-sys-user-open-id');
        $user->setUser($sysUser);
        $this->repository->save($user);

        $foundUser = $this->repository->getBySysUser($sysUser);

        self::assertInstanceOf(User::class, $foundUser);
        self::assertEquals('get-by-sys-user-open-id', $foundUser->getOpenId());
    }

    public function testGetBySysUserWithOpenIdMatch(): void
    {
        $sysUser = $this->createTestSystemUser();
        $user = $this->createTestUser('system-user-123');
        $this->repository->save($user);

        $foundUser = $this->repository->getBySysUser($sysUser);

        self::assertInstanceOf(User::class, $foundUser);
        self::assertEquals('system-user-123', $foundUser->getOpenId());
    }

    public function testGetBySysUserWithUnionIdMatch(): void
    {
        $sysUser = $this->createTestSystemUser();
        $user = $this->createTestUser('union-match-open-id', 'system-user-123');
        $this->repository->save($user);

        $foundUser = $this->repository->getBySysUser($sysUser);

        self::assertInstanceOf(User::class, $foundUser);
        self::assertEquals('system-user-123', $foundUser->getUnionId());
    }

    public function testGetBySysUserWithNoMatch(): void
    {
        $sysUser = $this->createTestSystemUser();

        $foundUser = $this->repository->getBySysUser($sysUser);

        self::assertNull($foundUser);
    }

    // Basic Repository Tests

    // FindAll Tests

    // FindOneBy Tests

    public function testFindOneByWithOrderBy(): void
    {
        $user1 = $this->createTestUser('order-test-1');
        $user1->setNickName('B User');
        $user2 = $this->createTestUser('order-test-2');
        $user2->setNickName('A User');
        $this->repository->save($user1);
        $this->repository->save($user2);

        $foundUser = $this->repository->findOneBy([], ['nickName' => 'ASC']);

        self::assertInstanceOf(User::class, $foundUser);
        self::assertEquals('A User', $foundUser->getNickName());
    }

    // FindBy Tests

    // Association Tests
    public function testFindByAssociatedMiniProgram(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $user = $this->createTestUser('association-test');
        $user->setAccount($miniProgram);
        $this->repository->save($user);

        $users = $this->repository->findBy(['account' => $miniProgram]);

        self::assertIsArray($users);
        self::assertGreaterThanOrEqual(1, count($users));
    }

    public function testFindByAssociatedSystemUser(): void
    {
        $sysUser = $this->createTestSystemUser();
        $user = $this->createTestUser('sys-user-association-test');
        $user->setUser($sysUser);
        $this->repository->save($user);

        $users = $this->repository->findBy(['user' => $sysUser]);

        self::assertIsArray($users);
        self::assertGreaterThanOrEqual(1, count($users));
    }

    public function testCountByAssociatedMiniProgram(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $user = $this->createTestUser('count-association-test');
        $user->setAccount($miniProgram);
        $this->repository->save($user);

        $count = $this->repository->count(['account' => $miniProgram]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    // Nullable Field Tests
    public function testFindByNullUnionId(): void
    {
        $user = $this->createTestUser('null-union-id-test');
        $user->setUnionId(null);
        $this->repository->save($user);

        $users = $this->repository->findBy(['unionId' => null]);

        self::assertIsArray($users);
        self::assertGreaterThanOrEqual(1, count($users));
    }

    public function testFindByNullNickName(): void
    {
        $user = $this->createTestUser('null-nickname-test');
        $user->setNickName(null);
        $this->repository->save($user);

        $users = $this->repository->findBy(['nickName' => null]);

        self::assertIsArray($users);
        self::assertGreaterThanOrEqual(1, count($users));
    }

    public function testCountByNullFields(): void
    {
        $user = $this->createTestUser('null-fields-count-test');
        $user->setUnionId(null);
        $user->setNickName(null);
        $this->repository->save($user);

        $count = $this->repository->count(['unionId' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociatedSystemUser(): void
    {
        $sysUser = $this->createTestSystemUser();
        $user = $this->createTestUser('count-sys-user-association-test');
        $user->setUser($sysUser);
        $this->repository->save($user);

        $count = $this->repository->count(['user' => $sysUser]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByAssociatedPhoneNumbers(): void
    {
        $user = $this->createTestUser('phone-association-test');
        $this->repository->save($user);

        $users = $this->repository->findAll();

        self::assertIsArray($users);
        self::assertGreaterThanOrEqual(1, count($users));
    }

    public function testCountByNullUnionId(): void
    {
        $user = $this->createTestUser('count-null-union-id-test');
        $user->setUnionId(null);
        $this->repository->save($user);

        $count = $this->repository->count(['unionId' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByNullNickName(): void
    {
        $user = $this->createTestUser('count-null-nickname-test');
        $user->setNickName(null);
        $this->repository->save($user);

        $count = $this->repository->count(['nickName' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $user = $this->createTestUser('count-association-account-test');
        $user->setAccount($miniProgram);
        $this->repository->save($user);

        $count = $this->repository->count(['account' => $miniProgram]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $user = $this->createTestUser('find-one-by-account-test');
        $user->setAccount($miniProgram);
        $this->repository->save($user);

        $foundUser = $this->repository->findOneBy(['account' => $miniProgram]);

        self::assertInstanceOf(User::class, $foundUser);
        self::assertEquals($miniProgram, $foundUser->getAccount());
    }

    protected function createNewEntity(): object
    {
        $entity = new User();
        $entity->setOpenId('test-' . uniqid());
        $entity->setNickName('Test User ' . uniqid());
        $entity->setLanguage(Language::zh_CN);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<User>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
