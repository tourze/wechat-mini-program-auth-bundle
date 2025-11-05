<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\EasyAdmin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use WechatMiniProgramAuthBundle\Controller\Admin\CodeSessionLogCrudController;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;

/**
 * @internal
 */
#[CoversClass(CodeSessionLogCrudController::class)]
#[RunTestsInSeparateProcesses] final class CodeSessionLogCrudConfigurationTest extends AbstractWebTestCase
{
    private CodeSessionLogCrudController $controller;

    public function testEntityFqcnConfiguration(): void
    {
        $this->assertEquals(
            CodeSessionLog::class,
            CodeSessionLogCrudController::getEntityFqcn()
        );
    }

    public function testControllerCanBeInstantiated(): void
    {
        $this->assertInstanceOf(CodeSessionLogCrudController::class, $this->controller);
    }

    public function testCrudConfiguration(): void
    {
        $crud = $this->controller->configureCrud(Crud::new());

        $this->assertInstanceOf(Crud::class, $crud);
    }

    public function testFieldsConfiguration(): void
    {
        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));

        $this->assertNotEmpty($fields);
        $this->assertGreaterThan(0, count($fields));

        // 检查所有字段都是有效的字段对象
        foreach ($fields as $field) {
            $this->assertIsObject($field);
        }
    }

    public function testActionsConfiguration(): void
    {
        $actions = $this->controller->configureActions(Actions::new());

        $this->assertInstanceOf(Actions::class, $actions);
    }

    public function testFiltersConfiguration(): void
    {
        $filters = $this->controller->configureFilters(Filters::new());

        $this->assertInstanceOf(Filters::class, $filters);
    }

    public function testConfigureFieldsForDifferentPages(): void
    {
        // 验证不同页面的字段配置都能正常执行
        $indexFields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));
        $newFields = iterator_to_array($this->controller->configureFields(Crud::PAGE_NEW));
        $editFields = iterator_to_array($this->controller->configureFields(Crud::PAGE_EDIT));
        $detailFields = iterator_to_array($this->controller->configureFields(Crud::PAGE_DETAIL));

        $this->assertNotEmpty($indexFields);
        $this->assertNotEmpty($newFields);
        $this->assertNotEmpty($editFields);
        $this->assertNotEmpty($detailFields);
    }

    public function testControllerInheritsFromAbstractCrudController(): void
    {
        $this->assertInstanceOf(
            AbstractCrudController::class,
            $this->controller
        );
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $this->assertEquals('INVALID', $method, 'EasyAdmin CRUD 控制器不应该有路由方法');
    }

    protected function onSetUp(): void
    {
        $this->controller = self::getService(CodeSessionLogCrudController::class);
    }
}
