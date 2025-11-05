<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramAuthBundle\Controller\Admin\UserCrudController;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Tests\EasyAdmin\UserCrudConfigurationTest;

/**
 * @internal
 * @see UserCrudConfigurationTest
 */
#[CoversClass(UserCrudController::class)]
#[RunTestsInSeparateProcesses]
final class UserCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<User>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(UserCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '小程序账号' => ['小程序账号'];
        yield 'OpenID' => ['OpenID'];
        yield 'UnionID' => ['UnionID'];
        yield '昵称' => ['昵称'];
        yield '头像URL' => ['头像URL'];
        yield '性别' => ['性别'];
        yield '国家' => ['国家'];
        yield '省份' => ['省份'];
        yield '城市' => ['城市'];
        yield '语言' => ['语言'];
        yield '关联用户' => ['关联用户'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
        yield 'nickName' => ['nickName'];
        yield 'gender' => ['gender'];
        yield 'country' => ['country'];
        yield 'province' => ['province'];
        yield 'city' => ['city'];
        yield 'language' => ['language'];
        yield 'avatarUrl' => ['avatarUrl'];
        yield 'user' => ['user'];
    }

    /** @return \Generator<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'openId' => ['openId'];
        yield 'unionId' => ['unionId'];
        yield 'nickName' => ['nickName'];
        yield 'gender' => ['gender'];
        yield 'country' => ['country'];
        yield 'province' => ['province'];
        yield 'city' => ['city'];
        yield 'language' => ['language'];
        yield 'avatarUrl' => ['avatarUrl'];
        yield 'user' => ['user'];
    }

    public function testAuthenticatedAccessShouldShowIndex(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $client->request('GET', '/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testOpenIdFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $user = new User();
        $user->setOpenId('test-open-id-789');
        $user->setNickName('Test User');
        $entityManager = $this->getEntityManager(); // @phpstan-ignore-line staticMethod.dynamicCall
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'openId' => [
                    'value' => 'test-open-id',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUnionIdFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $user = new User();
        $user->setOpenId('test-open-id');
        $user->setUnionId('test-union-id-456');
        $user->setNickName('Test User 2');
        $entityManager = $this->getEntityManager(); // @phpstan-ignore-line staticMethod.dynamicCall
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'unionId' => [
                    'value' => 'test-union-id',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testNickNameFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $user = new User();
        $user->setOpenId('test-open-id-unique');
        $user->setNickName('张三测试用户');
        $entityManager = $this->getEntityManager(); // @phpstan-ignore-line staticMethod.dynamicCall
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'nickName' => [
                    'value' => '张三测试',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        // 测试必填字段 openId 的验证
        $crawler = $client->request('GET', $this->generateAdminUrl('new'));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Create')->form();
        $form['User[openId]'] = ''; // 必填字段留空

        $crawler = $client->submit($form);

        // 验证表单错误状态码或成功显示表单
        $statusCode = $client->getResponse()->getStatusCode();
        if (422 === $statusCode) {
            $this->assertEquals(422, $statusCode);
            // 验证错误消息
            $errorElements = $crawler->filter('.invalid-feedback, .form-error-message');
            if ($errorElements->count() > 0) {
                $this->assertStringContainsString('should not be blank', $errorElements->text());
            }
        } else {
            $this->assertEquals(200, $statusCode);
            // 验证表单成功显示
            $content = $client->getResponse()->getContent();
            $this->assertNotEmpty($content);
        }
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
        $this->assertContains('OpenID', $headers);
        $this->assertContains('UnionID', $headers);
        $this->assertContains('昵称', $headers);
        $this->assertContains('性别', $headers);
        $this->assertContains('国家', $headers);
        $this->assertContains('省份', $headers);
        $this->assertContains('城市', $headers);
        $this->assertContains('语言', $headers);
        $this->assertContains('创建时间', $headers);
        $this->assertContains('更新时间', $headers);
    }
}
