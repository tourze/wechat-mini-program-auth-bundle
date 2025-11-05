<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use WechatMiniProgramAuthBundle\Entity\AuthLog;
use WechatMiniProgramAuthBundle\Repository\AuthLogRepository;

/**
 * @internal
 */
/**
 * @extends AbstractRepositoryTestCase<AuthLog>
 */
#[CoversClass(AuthLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class AuthLogRepositoryTest extends AbstractRepositoryTestCase
{
    private AuthLogRepository $repository;

    protected function onSetUp(): void
    {
        $container = self::getContainer();
        $repository = $container->get(AuthLogRepository::class);
        self::assertInstanceOf(AuthLogRepository::class, $repository);
        $this->repository = $repository;
    }

    private function createTestAuthLog(string $openId = 'test-open-id', ?string $rawData = null): AuthLog
    {
        $authLog = new AuthLog();
        $authLog->setOpenId($openId);
        if (null !== $rawData) {
            $authLog->setRawData($rawData);
        } else {
            $jsonData = json_encode(['test' => 'data']);
            if (false !== $jsonData) {
                $authLog->setRawData($jsonData);
            }
        }

        return $authLog;
    }

    public function testSave(): void
    {
        $authLog = $this->createTestAuthLog('save-test-open-id');

        $this->repository->save($authLog);

        $savedAuthLog = $this->repository->findOneBy(['openId' => 'save-test-open-id']);
        self::assertInstanceOf(AuthLog::class, $savedAuthLog);
        self::assertEquals('save-test-open-id', $savedAuthLog->getOpenId());
    }

    public function testSaveWithoutFlush(): void
    {
        $authLog = $this->createTestAuthLog('save-no-flush-open-id');

        $this->repository->save($authLog, false);

        $savedAuthLog = $this->repository->findOneBy(['openId' => 'save-no-flush-open-id']);
        self::assertNull($savedAuthLog);
    }

    public function testRemove(): void
    {
        $authLog = $this->createTestAuthLog('remove-test-open-id');
        $this->repository->save($authLog);

        $this->repository->remove($authLog);

        $removedAuthLog = $this->repository->findOneBy(['openId' => 'remove-test-open-id']);
        self::assertNull($removedAuthLog);
    }

    // Basic Repository Tests

    // FindAll Tests

    // FindOneBy Tests

    public function testFindOneByWithOrderBy(): void
    {
        $uniquePrefix = 'order-test-' . uniqid();
        $authLog1 = $this->createTestAuthLog($uniquePrefix . '-1');
        $authLog1->setRawData('{"order": "B"}');
        $authLog2 = $this->createTestAuthLog($uniquePrefix . '-2');
        $authLog2->setRawData('{"order": "A"}');
        $this->repository->save($authLog1);
        $this->repository->save($authLog2);

        $foundAuthLog = $this->repository->findOneBy(
            ['openId' => [$uniquePrefix . '-1', $uniquePrefix . '-2']],
            ['rawData' => 'ASC']
        );

        self::assertInstanceOf(AuthLog::class, $foundAuthLog);
        self::assertEquals('{"order": "A"}', $foundAuthLog->getRawData());
    }

    // FindBy Tests

    // Nullable Field Tests
    public function testFindByNullOpenId(): void
    {
        $authLog = new AuthLog();
        $authLog->setRawData('test-data');
        $this->repository->save($authLog);

        $authLogs = $this->repository->findBy(['openId' => null]);

        self::assertGreaterThanOrEqual(1, count($authLogs));
    }

    public function testFindByNullRawData(): void
    {
        $authLog = new AuthLog();
        $authLog->setOpenId('null-raw-data-test');
        $this->repository->save($authLog);

        $authLogs = $this->repository->findBy(['rawData' => null]);

        self::assertGreaterThanOrEqual(1, count($authLogs));
    }

    public function testCountByNullFields(): void
    {
        $authLog = new AuthLog();
        $this->repository->save($authLog);

        $count = $this->repository->count(['openId' => null]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    protected function createNewEntity(): object
    {
        $entity = new AuthLog();
        $entity->setOpenId('test-' . uniqid());
        $rawData = json_encode(['test' => 'data']);
        $entity->setRawData(false !== $rawData ? $rawData : '');

        return $entity;
    }

    /**
     * @return ServiceEntityRepository<AuthLog>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
