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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Enum\Gender;
use WechatMiniProgramAuthBundle\Enum\Language;

/**
 * 微信小程序用户管理
 *
 * @extends AbstractCrudController<User>
 */
#[AdminCrud(routePath: '/wechat-mini-program-auth/user', routeName: 'wechat_mini_program_auth_user')]
final class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('微信小程序用户')
            ->setEntityLabelInPlural('微信小程序用户列表')
            ->setPageTitle('index', '微信小程序用户列表')
            ->setPageTitle('new', '创建微信小程序用户')
            ->setPageTitle('edit', '编辑微信小程序用户')
            ->setPageTitle('detail', '微信小程序用户详情')
            ->setHelp('index', '管理微信小程序授权的用户信息')
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['id', 'openId', 'unionId', 'nickName'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本字段
        yield IdField::new('id', 'ID')
            ->setMaxLength(9999)
            ->hideOnForm()
        ;

        yield AssociationField::new('account', '小程序账号')
            ->setRequired(true)
            ->autocomplete()
        ;

        yield TextField::new('openId', 'OpenID')
            ->setRequired(true)
            ->setHelp('微信用户在该小程序的唯一标识')
        ;

        yield TextField::new('unionId', 'UnionID')
            ->setHelp('微信开放平台唯一标识，需绑定开放平台')
        ;

        yield TextField::new('nickName', '昵称');

        yield ImageField::new('avatarUrl', '头像')
            ->setBasePath('')
            ->setUploadDir('public/uploads/wechat-avatars')
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->onlyOnDetail()
        ;

        yield TextField::new('avatarUrl', '头像URL')
            ->hideOnDetail()
        ;

        yield ChoiceField::new('gender', '性别')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => Gender::class])
            ->formatValue(function ($value) {
                return $value instanceof Gender ? $value->getLabel() : '';
            })
        ;

        yield TextField::new('country', '国家');

        yield TextField::new('province', '省份');

        yield TextField::new('city', '城市');

        yield ChoiceField::new('language', '语言')
            ->setFormType(EnumType::class)
            ->setFormTypeOptions(['class' => Language::class])
            ->formatValue(function ($value) {
                return $value instanceof Language ? $value->getLabel() : '';
            })
        ;

        yield Field::new('authorizeScopes', '已授权权限')
            ->formatValue(function ($value) {
                if ('' === $value || null === $value) {
                    return '无';
                }

                if (!is_array($value)) {
                    return '格式错误';
                }

                return implode(', ', $value);
            })
            ->onlyOnDetail()
        ;

        yield TextareaField::new('rawData', '原始数据')
            ->setHelp('微信返回的原始JSON数据')
            ->onlyOnDetail()
        ;

        // 关联字段
        yield AssociationField::new('phoneNumbers', '手机号码')
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                if (!$entity instanceof User) {
                    return '无';
                }
                $phoneNumbers = $entity->getPhoneNumbers();
                if ($phoneNumbers->isEmpty()) {
                    return '无';
                }
                $phones = [];
                foreach ($phoneNumbers as $phone) {
                    $phones[] = $phone->getPhoneNumber();
                }

                return implode(', ', $phones);
            })
        ;

        yield AssociationField::new('user', '关联用户');

        // IP和时间戳字段
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
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        // 性别选项
        $genderChoices = [];
        foreach (Gender::cases() as $case) {
            $genderChoices[$case->getLabel()] = $case->value;
        }

        // 语言选项
        $languageChoices = [];
        foreach (Language::cases() as $case) {
            $languageChoices[$case->getLabel()] = $case->value;
        }

        return $filters
            ->add(EntityFilter::new('account', '小程序账号'))
            ->add(TextFilter::new('openId', 'OpenID'))
            ->add(TextFilter::new('unionId', 'UnionID'))
            ->add(TextFilter::new('nickName', '昵称'))
            ->add(ChoiceFilter::new('gender', '性别')->setChoices($genderChoices))
            ->add(ChoiceFilter::new('language', '语言')->setChoices($languageChoices))
            ->add(TextFilter::new('country', '国家'))
            ->add(TextFilter::new('province', '省份'))
            ->add(TextFilter::new('city', '城市'))
            ->add(EntityFilter::new('user', '关联用户'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
            ->add(DateTimeFilter::new('updateTime', '更新时间'))
        ;
    }
}
