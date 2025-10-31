<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramAuthBundle\Controller\Admin\CodeSessionLogCrudController;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Tests\EasyAdmin\CodeSessionLogCrudConfigurationTest;

/**
 * @internal
 * @see CodeSessionLogCrudConfigurationTest
 *
 * CodeSessionLogCrudController 是只读控制器，禁用了 NEW 和 EDIT 操作。
 * 因此，这些操作的验证测试不适用，将被跳过。
 *
 * @phpstan-ignore-next-line Controller has required fields but no validation test (read-only controller)
 */
#[CoversClass(CodeSessionLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class CodeSessionLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<CodeSessionLog>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(CodeSessionLogCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '小程序账号' => ['小程序账号'];
        yield 'Code' => ['Code'];
        yield 'OpenID' => ['OpenID'];
        yield 'UnionID' => ['UnionID'];
        yield '创建时间' => ['创建时间'];
    }

    /**
     * 虽然 CodeSessionLogCrudController 是只读控制器，但需要提供数据以避免数据提供器验证失败
     * @return \Generator<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'code' => ['code'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
    }

    public function testAuthenticatedAccessShouldShowIndex(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $client->request('GET', '/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCodeFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCode('test-code-123');
        $codeSessionLog->setOpenId('test-open-id');
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($codeSessionLog);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'code' => [
                    'value' => 'test-code',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testOpenIdFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $codeSessionLog = new CodeSessionLog();
        $codeSessionLog->setCode('test-code');
        $codeSessionLog->setOpenId('search-open-id-456');
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($codeSessionLog);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'openId' => [
                    'value' => 'search-open-id',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testIndexPageHeaders(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrl('index'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $crawler->filter('table thead th')->each(
            static function (Crawler $node): string {
                return trim($node->text());
            }
        );

        $this->assertContains('ID', $headers);
        $this->assertContains('小程序账号', $headers);
        $this->assertContains('Code', $headers);
        $this->assertContains('OpenID', $headers);
        $this->assertContains('UnionID', $headers);
        $this->assertContains('创建时间', $headers);
    }

    /**
     * 虽然 CodeSessionLogCrudController 是只读控制器，但需要提供数据以避免数据提供器验证失败
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'code' => ['code'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
    }
}
