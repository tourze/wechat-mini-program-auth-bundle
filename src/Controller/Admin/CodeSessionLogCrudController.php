<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;

/**
 * code2session日志管理
 *
 * @extends AbstractCrudController<CodeSessionLog>
 */
#[AdminCrud(routePath: '/wechat-mini-program-auth/code-session-log', routeName: 'wechat_mini_program_auth_code_session_log')]
final class CodeSessionLogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CodeSessionLog::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('code2session日志')
            ->setEntityLabelInPlural('code2session日志列表')
            ->setPageTitle('index', 'code2session日志列表')
            ->setPageTitle('new', '创建code2session日志')
            ->setPageTitle('edit', '编辑code2session日志')
            ->setPageTitle('detail', 'code2session日志详情')
            ->setHelp('index', '记录微信小程序登录时的code2session调用日志')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'code', 'openId', 'unionId'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('account', '小程序账号')
            ->setRequired(true)
            ->autocomplete()
        ;

        yield TextField::new('code', 'Code')
            ->setRequired(true)
            ->setHelp('微信登录时的临时授权码')
        ;

        yield TextField::new('openId', 'OpenID')
            ->setHelp('解析后的用户OpenID')
        ;

        yield TextField::new('unionId', 'UnionID')
            ->setHelp('解析后的用户UnionID')
        ;

        yield TextField::new('sessionKey', 'SessionKey')
            ->onlyOnDetail()
            ->setHelp('微信返回的会话密钥，用于数据解密')
        ;

        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('微信API返回的原始JSON数据')
            ->onlyOnDetail()
        ;

        yield TextField::new('scene', '场景值')
            ->onlyOnDetail()
        ;

        yield TextField::new('query', '启动参数')
            ->formatValue(function ($value) {
                if ('' === $value || null === $value) {
                    return '无';
                }

                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            })
            ->onlyOnDetail()
        ;

        yield TextField::new('path', '页面路径')
            ->onlyOnDetail()
        ;

        yield TextField::new('referrerInfo', '来源信息')
            ->formatValue(function ($value) {
                if ('' === $value || null === $value) {
                    return '无';
                }

                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            })
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
            ->add(EntityFilter::new('account', '小程序账号'))
            ->add(TextFilter::new('code', 'Code'))
            ->add(TextFilter::new('openId', 'OpenID'))
            ->add(TextFilter::new('unionId', 'UnionID'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
