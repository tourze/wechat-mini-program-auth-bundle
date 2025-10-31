<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use WechatMiniProgramAuthBundle\Entity\PhoneNumber;

/**
 * 授权手机号管理
 *
 * @extends AbstractCrudController<PhoneNumber>
 */
#[AdminCrud(routePath: '/wechat-mini-program-auth/phone-number', routeName: 'wechat_mini_program_auth_phone_number')]
final class PhoneNumberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PhoneNumber::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('授权手机号')
            ->setEntityLabelInPlural('授权手机号列表')
            ->setPageTitle('index', '授权手机号列表')
            ->setPageTitle('new', '创建授权手机号')
            ->setPageTitle('edit', '编辑授权手机号')
            ->setPageTitle('detail', '授权手机号详情')
            ->setHelp('index', '管理用户授权的手机号码信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'phoneNumber', 'purePhoneNumber'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield TextField::new('phoneNumber', '手机号')
            ->setRequired(true)
            ->setHelp('完整手机号，包含国际区号')
        ;

        yield TextField::new('purePhoneNumber', '纯手机号')
            ->setHelp('不含区号的手机号码')
        ;

        yield TextField::new('countryCode', '国家区号')
            ->setHelp('如：86（中国）')
        ;

        yield ArrayField::new('watermark', '数据水印')
            ->onlyOnDetail()
        ;

        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('微信返回的原始JSON数据')
            ->onlyOnDetail()
        ;

        yield TextField::new('scene', '场景值')
            ->onlyOnDetail()
        ;

        yield ArrayField::new('query', '启动参数')
            ->onlyOnDetail()
        ;

        yield TextField::new('path', '页面路径')
            ->onlyOnDetail()
        ;

        yield ArrayField::new('referrerInfo', '来源信息')
            ->onlyOnDetail()
        ;

        yield AssociationField::new('users', '关联用户')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                if (!$entity instanceof PhoneNumber) {
                    return '无';
                }
                $users = $entity->getUsers();
                if ($users->isEmpty()) {
                    return '无';
                }
                $userNames = [];
                foreach ($users as $user) {
                    $userNames[] = (string) $user;
                }

                return implode(', ', $userNames);
            })
        ;

        yield TextField::new('createFromIp', '创建IP')
            ->onlyOnDetail()
        ;

        yield TextField::new('updateFromIp', '更新IP')
            ->onlyOnDetail()
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
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
            ->add(TextFilter::new('phoneNumber', '手机号'))
            ->add(TextFilter::new('purePhoneNumber', '纯手机号'))
            ->add(TextFilter::new('countryCode', '国家区号'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
