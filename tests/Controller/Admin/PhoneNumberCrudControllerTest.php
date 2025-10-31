<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DomCrawler\Crawler;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use WechatMiniProgramAuthBundle\Controller\Admin\PhoneNumberCrudController;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;
use WechatMiniProgramAuthBundle\Tests\EasyAdmin\PhoneNumberCrudConfigurationTest;

/**
 * @internal
 * @see PhoneNumberCrudConfigurationTest
 *
 * PhoneNumberCrudController 是只读控制器，禁用了 NEW 和 EDIT 操作。
 * 因此，这些操作的验证测试不适用，将被跳过。
 *
 * @phpstan-ignore-next-line Controller has required fields but no validation test (read-only controller)
 */
#[CoversClass(PhoneNumberCrudController::class)]
#[RunTestsInSeparateProcesses]
final class PhoneNumberCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<PhoneNumber>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(PhoneNumberCrudController::class);
    }

    /** @return \Generator<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '手机号' => ['手机号'];
        yield '纯手机号' => ['纯手机号'];
        yield '国家区号' => ['国家区号'];
        yield '创建时间' => ['创建时间'];
        yield '更新时间' => ['更新时间'];
    }

    /**
     * 虽然 PhoneNumberCrudController 是只读控制器，但需要提供数据以避免数据提供器验证失败
     * @return \Generator<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield 'phoneNumber' => ['phoneNumber'];
        yield 'purePhoneNumber' => ['purePhoneNumber'];
        yield 'countryCode' => ['countryCode'];
    }

    public function testAuthenticatedAccessShouldShowIndex(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $client->request('GET', '/admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPhoneNumberFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $phoneNumber = new PhoneNumber();
        $phoneNumber->setPhoneNumber('+86-13812345678');
        $phoneNumber->setPurePhoneNumber('13812345678');
        $phoneNumber->setCountryCode('86');
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($phoneNumber);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'phoneNumber' => [
                    'value' => '+86-138',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testPurePhoneNumberFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $phoneNumber = new PhoneNumber();
        $phoneNumber->setPhoneNumber('+86-13987654321');
        $phoneNumber->setPurePhoneNumber('13987654321');
        $phoneNumber->setCountryCode('86');
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($phoneNumber);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'purePhoneNumber' => [
                    'value' => '139876',
                ],
            ],
        ]);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testCountryCodeFilterSearch(): void
    {
        $client = self::createClientWithDatabase();
        $admin = $this->createAdminUser('admin@example.com', 'admin123');
        $this->loginAsAdmin($client, 'admin@example.com', 'admin123');

        $phoneNumber = new PhoneNumber();
        $phoneNumber->setPhoneNumber('+1-5551234567');
        $phoneNumber->setPurePhoneNumber('5551234567');
        $phoneNumber->setCountryCode('1');
        /** @phpstan-ignore-next-line */
        $entityManager = $this->getEntityManager();
        $entityManager->persist($phoneNumber);
        $entityManager->flush();

        $client->request('GET', '/admin', [
            'filters' => [
                'countryCode' => [
                    'value' => '1',
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
        $this->assertContains('手机号', $headers);
        $this->assertContains('纯手机号', $headers);
        $this->assertContains('国家区号', $headers);
        $this->assertContains('创建时间', $headers);
        $this->assertContains('更新时间', $headers);
    }

    /**
     * 虽然 PhoneNumberCrudController 是只读控制器，但需要提供数据以避免数据提供器验证失败
     * @return \Generator<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield 'phoneNumber' => ['phoneNumber'];
        yield 'purePhoneNumber' => ['purePhoneNumber'];
        yield 'countryCode' => ['countryCode'];
    }
}
