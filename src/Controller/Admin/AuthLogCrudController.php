<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramAuthBundle\Entity\AuthLog;

/**
 * 授权日志管理
 *
 * @extends AbstractCrudController<AuthLog>
 */
#[AdminCrud(routePath: '/wechat-mini-program-auth/auth-log', routeName: 'wechat_mini_program_auth_auth_log')]
final class AuthLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AuthLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('授权日志')
            ->setEntityLabelInPlural('授权日志列表')
            ->setPageTitle('index', '授权日志列表')
            ->setPageTitle('new', '创建授权日志')
            ->setPageTitle('edit', '编辑授权日志')
            ->setPageTitle('detail', '授权日志详情')
            ->setHelp('index', '记录微信小程序用户的授权行为日志')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'openId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('openId', 'OpenID')
            ->setRequired(true)
            ->setHelp('授权用户的微信OpenID')
        ;

        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('授权时的原始请求数据')
            ->onlyOnDetail()
        ;

        yield TextField::new('createdBy', '创建用户')
            ->onlyOnDetail()
        ;

        yield TextField::new('createdFromIp', '创建IP')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('openId', 'OpenID'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
