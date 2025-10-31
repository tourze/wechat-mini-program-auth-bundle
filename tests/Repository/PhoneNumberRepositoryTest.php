<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;
use WechatMiniProgramBundle\Entity\Account;

/**
 * @internal
 */
#[CoversClass(PhoneNumberRepository::class)]
#[RunTestsInSeparateProcesses]
final class PhoneNumberRepositoryTest extends AbstractRepositoryTestCase
{
    private PhoneNumberRepository $repository;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(PhoneNumberRepository::class);
        self::assertInstanceOf(PhoneNumberRepository::class, $repository);
        $this->repository = $repository;
    }

    private function createTestPhoneNumber(string $phoneNumber = '+8613800138000', ?string $purePhoneNumber = null): PhoneNumber
    {
        $phone = new PhoneNumber();
        $phone->setPhoneNumber($phoneNumber);
        if (null !== $purePhoneNumber) {
            $phone->setPurePhoneNumber($purePhoneNumber);
        }
        $phone->setCountryCode('+86');
        $phone->setWatermark(['test' => 'watermark']);
        $phone->setRawData('{"phoneNumber":"' . $phoneNumber . '"}');

        return $phone;
    }

    private function createTestUser(string $openId = 'test-user-open-id'): User
    {
        $user = new User();
        $user->setOpenId($openId);
        $user->setNickName('Test User');

        // 添加 MiniProgram 账户
        $miniProgram = new Account();
        $miniProgram->setName('Test Mini Program');
        $miniProgram->setAppId('test-app-id-' . $openId);
        $miniProgram->setAppSecret('test-app-secret');
        $miniProgram->setValid(true);
        $user->setAccount($miniProgram);

        return $user;
    }

    public function testSave(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138001', '13800138001');

        $this->repository->save($phone);

        $savedPhone = $this->repository->findOneBy(['phoneNumber' => '+8613800138001']);
        self::assertInstanceOf(PhoneNumber::class, $savedPhone);
        self::assertEquals('+8613800138001', $savedPhone->getPhoneNumber());
        self::assertEquals('13800138001', $savedPhone->getPurePhoneNumber());
    }

    public function testSaveWithoutFlush(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138002');

        $this->repository->save($phone, false);

        $savedPhone = $this->repository->findOneBy(['phoneNumber' => '+8613800138002']);
        self::assertNull($savedPhone);
    }

    public function testRemove(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138003');
        $this->repository->save($phone);

        $this->repository->remove($phone);

        $removedPhone = $this->repository->findOneBy(['phoneNumber' => '+8613800138003']);
        self::assertNull($removedPhone);
    }

    // Basic Repository Tests

    // FindAll Tests

    // FindOneBy Tests

    public function testFindOneByWithOrderBy(): void
    {
        $phone1 = $this->createTestPhoneNumber('+8613800138009');
        $phone1->setPurePhoneNumber('13800138009');
        $phone2 = $this->createTestPhoneNumber('+8613800138010');
        $phone2->setPurePhoneNumber('13800138010');
        $this->repository->save($phone1);
        $this->repository->save($phone2);

        $foundPhone = $this->repository->findOneBy([], ['purePhoneNumber' => 'ASC']);

        self::assertInstanceOf(PhoneNumber::class, $foundPhone);
        self::assertEquals('13800138009', $foundPhone->getPurePhoneNumber());
    }

    // FindBy Tests

    // Association Tests
    public function testFindByAssociatedUsers(): void
    {
        $user = $this->createTestUser('association-test-user');
        $phone = $this->createTestPhoneNumber('+8613800138024');

        // 先保存 MiniProgram 和用户实体
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $account = $user->getAccount();
        self::assertNotNull($account);
        $entityManager->persist($account);
        $entityManager->persist($user);
        $entityManager->flush();

        $phone->addUser($user);
        $this->repository->save($phone);

        // 使用 DQL 查询来测试关联关系，因为 findBy() 不支持 Many-to-Many 关联查询
        $dql = 'SELECT p FROM WechatMiniProgramAuthBundle\Entity\PhoneNumber p JOIN p.users u WHERE u = :user';
        $phones = $entityManager->createQuery($dql)
            ->setParameter('user', $user)
            ->getResult()
        ;

        self::assertIsArray($phones);
        self::assertGreaterThanOrEqual(1, count($phones));
    }

    public function testCountByAssociatedUsers(): void
    {
        $user = $this->createTestUser('count-association-test-user');
        $phone = $this->createTestPhoneNumber('+8613800138025');

        // 先保存 MiniProgram 和用户实体
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $account = $user->getAccount();
        self::assertNotNull($account);
        $entityManager->persist($account);
        $entityManager->persist($user);
        $entityManager->flush();

        $phone->addUser($user);
        $this->repository->save($phone);

        // 使用 DQL 查询来测试关联关系计数
        $dql = 'SELECT COUNT(p) FROM WechatMiniProgramAuthBundle\Entity\PhoneNumber p JOIN p.users u WHERE u = :user';
        $count = $entityManager->createQuery($dql)
            ->setParameter('user', $user)
            ->getSingleScalarResult()
        ;

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    // Nullable Field Tests
    public function testFindByNullPurePhoneNumber(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138026');
        $phone->setPurePhoneNumber(null);
        $this->repository->save($phone);

        $phones = $this->repository->findBy(['purePhoneNumber' => null]);

        self::assertIsArray($phones);
        self::assertGreaterThanOrEqual(1, count($phones));
    }

    public function testFindByNullCountryCode(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138027');
        $phone->setCountryCode(null);
        $this->repository->save($phone);

        $phones = $this->repository->findBy(['countryCode' => null]);

        self::assertIsArray($phones);
        self::assertGreaterThanOrEqual(1, count($phones));
    }

    public function testFindByNullWatermark(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138028');
        $phone->setWatermark(null);
        $this->repository->save($phone);

        $phones = $this->repository->findBy(['watermark' => null]);

        self::assertIsArray($phones);
        self::assertGreaterThanOrEqual(1, count($phones));
    }

    public function testFindByNullRawData(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138029');
        $phone->setRawData(null);
        $this->repository->save($phone);

        $phones = $this->repository->findBy(['rawData' => null]);

        self::assertIsArray($phones);
        self::assertGreaterThanOrEqual(1, count($phones));
    }

    public function testCountByNullFields(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138030');
        $phone->setPurePhoneNumber(null);
        $phone->setCountryCode(null);
        $this->repository->save($phone);

        $count = $this->repository->count(['purePhoneNumber' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByNullPurePhoneNumber(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138031');
        $phone->setPurePhoneNumber(null);
        $this->repository->save($phone);

        $count = $this->repository->count(['purePhoneNumber' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByNullCountryCode(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138032');
        $phone->setCountryCode(null);
        $this->repository->save($phone);

        $count = $this->repository->count(['countryCode' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByNullWatermark(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138033');
        $phone->setWatermark(null);
        $this->repository->save($phone);

        $count = $this->repository->count(['watermark' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByNullRawData(): void
    {
        $phone = $this->createTestPhoneNumber('+8613800138034');
        $phone->setRawData(null);
        $this->repository->save($phone);

        $count = $this->repository->count(['rawData' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationUsersShouldReturnCorrectNumber(): void
    {
        $user = $this->createTestUser('count-association-users-test');
        $phone = $this->createTestPhoneNumber('+8613800138036');

        // 先保存 MiniProgram 和用户实体
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $account = $user->getAccount();
        self::assertNotNull($account);
        $entityManager->persist($account);
        $entityManager->persist($user);
        $entityManager->flush();

        $phone->addUser($user);
        $this->repository->save($phone);

        // 使用 DQL 查询来测试关联关系计数
        $dql = 'SELECT COUNT(p) FROM WechatMiniProgramAuthBundle\Entity\PhoneNumber p JOIN p.users u WHERE u = :user';
        $count = $entityManager->createQuery($dql)
            ->setParameter('user', $user)
            ->getSingleScalarResult()
        ;

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationUsersShouldReturnMatchingEntity(): void
    {
        $user = $this->createTestUser('find-one-by-users-test');
        $phone = $this->createTestPhoneNumber('+8613800138037');

        // 先保存 MiniProgram 和用户实体
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $account = $user->getAccount();
        self::assertNotNull($account);
        $entityManager->persist($account);
        $entityManager->persist($user);
        $entityManager->flush();

        $phone->addUser($user);
        $this->repository->save($phone);

        // 使用 DQL 查询来测试关联关系
        $dql = 'SELECT p FROM WechatMiniProgramAuthBundle\Entity\PhoneNumber p JOIN p.users u WHERE u = :user';
        $foundPhone = $entityManager->createQuery($dql)
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;

        self::assertInstanceOf(PhoneNumber::class, $foundPhone);
        self::assertTrue($foundPhone->getUsers()->contains($user));
    }

    protected function createNewEntity(): object
    {
        $entity = new PhoneNumber();
        $entity->setPhoneNumber('+86138' . str_pad(strval(rand(0, 99999999)), 8, '0', STR_PAD_LEFT));
        $entity->setCountryCode('+86');
        $rawData = json_encode(['phoneNumber' => $entity->getPhoneNumber()]);
        $entity->setRawData(false !== $rawData ? $rawData : null);

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<PhoneNumber>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
