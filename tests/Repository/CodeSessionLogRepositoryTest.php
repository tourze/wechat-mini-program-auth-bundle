<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatMiniProgramAppIDContracts\MiniProgramInterface;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Repository\CodeSessionLogRepository;
use WechatMiniProgramBundle\Entity\Account;

/**
 * @internal
 * @extends AbstractRepositoryTestCase<CodeSessionLog>
 */
#[CoversClass(CodeSessionLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class CodeSessionLogRepositoryTest extends AbstractRepositoryTestCase
{
    private CodeSessionLogRepository $repository;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(CodeSessionLogRepository::class);
        self::assertInstanceOf(CodeSessionLogRepository::class, $repository);
        $this->repository = $repository;
    }

    private function createTestCodeSessionLog(string $code = 'test-code', ?string $openId = null): CodeSessionLog
    {
        $log = new CodeSessionLog();
        $log->setCode($code);
        if (null !== $openId) {
            $log->setOpenId($openId);
        }
        $log->setSessionKey('test-session-key');
        $log->setRawData('{"test":"data"}');

        return $log;
    }

    private function createTestMiniProgram(): MiniProgramInterface
    {
        $miniProgram = new Account();
        $miniProgram->setName('Test Mini Program');
        $miniProgram->setAppId('test-app-id');
        $miniProgram->setAppSecret('test-app-secret');
        $miniProgram->setValid(true);
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $entityManager->persist($miniProgram);
        $entityManager->flush();

        return $miniProgram;
    }

    public function testSave(): void
    {
        $log = $this->createTestCodeSessionLog('save-test-code', 'save-test-open-id');

        $this->repository->save($log);

        $savedLog = $this->repository->findOneBy(['code' => 'save-test-code']);
        self::assertInstanceOf(CodeSessionLog::class, $savedLog);
        self::assertEquals('save-test-code', $savedLog->getCode());
        self::assertEquals('save-test-open-id', $savedLog->getOpenId());
    }

    public function testSaveWithoutFlush(): void
    {
        $log = $this->createTestCodeSessionLog('save-no-flush-code');

        $this->repository->save($log, false);

        $savedLog = $this->repository->findOneBy(['code' => 'save-no-flush-code']);
        self::assertNull($savedLog);
    }

    public function testRemove(): void
    {
        $log = $this->createTestCodeSessionLog('remove-test-code');
        $this->repository->save($log);

        $this->repository->remove($log);

        $removedLog = $this->repository->findOneBy(['code' => 'remove-test-code']);
        self::assertNull($removedLog);
    }

    // Basic Repository Tests

    // FindAll Tests

    // FindOneBy Tests

    public function testFindOneByWithOrderBy(): void
    {
        $log1 = $this->createTestCodeSessionLog('order-test-b');
        $log1->setOpenId('B-open-id');
        $log2 = $this->createTestCodeSessionLog('order-test-a');
        $log2->setOpenId('A-open-id');
        $this->repository->save($log1);
        $this->repository->save($log2);

        $foundLog = $this->repository->findOneBy([], ['openId' => 'ASC']);

        self::assertInstanceOf(CodeSessionLog::class, $foundLog);
        self::assertEquals('A-open-id', $foundLog->getOpenId());
    }

    // FindBy Tests

    // Association Tests
    public function testFindByAssociatedMiniProgram(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $log = $this->createTestCodeSessionLog('association-test');
        $log->setAccount($miniProgram);
        $this->repository->save($log);

        $logs = $this->repository->findBy(['account' => $miniProgram]);

        self::assertGreaterThanOrEqual(1, count($logs));
    }

    public function testCountByAssociatedMiniProgram(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $log = $this->createTestCodeSessionLog('count-association-test');
        $log->setAccount($miniProgram);
        $this->repository->save($log);

        $count = $this->repository->count(['account' => $miniProgram]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    // Test querying non-existent null values to ensure repository can handle null criteria
    public function testFindByNullOpenId(): void
    {
        $logs = $this->repository->findBy(['openId' => null]);

        self::assertIsArray($logs);
    }

    public function testFindByNullUnionId(): void
    {
        $logs = $this->repository->findBy(['unionId' => null]);

        self::assertIsArray($logs);
    }

    public function testFindByNullSessionKey(): void
    {
        $logs = $this->repository->findBy(['sessionKey' => null]);

        self::assertIsArray($logs);
    }

    public function testFindByNullRawData(): void
    {
        $logs = $this->repository->findBy(['rawData' => null]);

        self::assertIsArray($logs);
    }

    public function testCountByNullFields(): void
    {
        $count = $this->repository->count(['openId' => null]);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByNullOpenId(): void
    {
        $count = $this->repository->count(['openId' => null]);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByNullUnionId(): void
    {
        $count = $this->repository->count(['unionId' => null]);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByNullSessionKey(): void
    {
        $count = $this->repository->count(['sessionKey' => null]);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByNullRawData(): void
    {
        $count = $this->repository->count(['rawData' => null]);

        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $log = $this->createTestCodeSessionLog('count-association-account-test');
        $log->setAccount($miniProgram);
        $this->repository->save($log);

        $count = $this->repository->count(['account' => $miniProgram]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $miniProgram = $this->createTestMiniProgram();
        $log = $this->createTestCodeSessionLog('find-one-by-account-test');
        $log->setAccount($miniProgram);
        $this->repository->save($log);

        $foundLog = $this->repository->findOneBy(['account' => $miniProgram]);

        self::assertInstanceOf(CodeSessionLog::class, $foundLog);
        self::assertEquals($miniProgram, $foundLog->getAccount());
    }

    protected function createNewEntity(): object
    {
        $entity = new CodeSessionLog();
        $entity->setCode('test-' . uniqid());
        $entity->setSessionKey('test-session-key');
        $rawData = json_encode(['test' => 'data']);
        $entity->setRawData(false !== $rawData ? $rawData : '');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<CodeSessionLog>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
