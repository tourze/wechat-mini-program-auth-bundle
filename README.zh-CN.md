# wechat-mini-program-auth-bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-%5E6.4-blue.svg)](https://symfony.com)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/test.yml?branch=master)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master)](https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

微信小程序授权认证 Symfony Bundle

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [配置](#配置)
- [使用方法](#使用方法)
  - [1. Code 换取 Session](#1-code-换取-session)
  - [2. 获取当前用户](#2-获取当前用户)
  - [3. 上传手机号](#3-上传手机号)
- [高级用法](#高级用法)
  - [自定义事件处理器](#自定义事件处理器)
  - [与用户管理系统集成](#与用户管理系统集成)
- [实体类](#实体类)
- [事件](#事件)
- [可用的 Procedure](#可用的-procedure)
- [安全性](#安全性)
- [错误处理](#错误处理)
- [系统要求](#系统要求)
- [许可证](#许可证)

## 功能特性

- 微信小程序用户认证
- Code 换取 Session 功能
- 用户资料管理
- 手机号绑定与验证
- 数据加密/解密服务
- 事件驱动架构，支持自定义扩展
- 完善的日志记录，便于调试

## 安装

```bash
composer require tourze/wechat-mini-program-auth-bundle
```

## 配置

### 1. 注册 Bundle

在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    WechatMiniProgramAuthBundle\WechatMiniProgramAuthBundle::class => ['all' => true],
];
```

### 2. 配置服务

Bundle 为所有服务提供自动配置。主要服务包括：

- `EncryptService`：处理微信数据解密
- `WechatTextFormatter`：格式化微信特定文本
- `UserService`：管理微信小程序用户创建和持久化
- `UserTransformService`：在微信用户和系统用户之间转换

## 使用方法

### 1. Code 换取 Session

将微信授权码转换为会话：

```php
use WechatMiniProgramAuthBundle\Procedure\WechatMiniProgramCodeToSession;

// 通过 JSON-RPC 调用
$result = $procedure->execute([
    'code' => 'authorization_code',
    'rawData' => '{"nickName":"用户",...}',
    'signature' => 'signature_string',
    'encryptedData' => 'encrypted_data',
    'iv' => 'initialization_vector'
]);
```

### 2. 获取当前用户

获取当前已认证的微信小程序用户：

```php
use WechatMiniProgramAuthBundle\Procedure\GetCurrentWechatMiniProgramUser;

$user = $procedure->execute();
```

### 3. 上传手机号

上传并绑定用户手机号：

```php
use WechatMiniProgramAuthBundle\Procedure\UploadWechatMiniProgramPhoneNumber;

$result = $procedure->execute([
    'encryptedData' => 'encrypted_phone_data',
    'iv' => 'initialization_vector'
]);
```

## 高级用法

### 自定义事件处理器

监听认证事件：

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WechatMiniProgramAuthBundle\Event\CodeToSessionResponseEvent;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CodeToSessionResponseEvent::class => 'onUserAuthenticated',
        ];
    }

    public function onUserAuthenticated(CodeToSessionResponseEvent $event): void
    {
        // 用户认证后的自定义逻辑
        $user = $event->getWechatUser();
        // ...
    }
}
```

### 与用户管理系统集成

扩展用户仓储以实现自定义用户创建：

```php
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use Tourze\UserServiceContracts\UserManagerInterface;

class CustomUserRepository extends UserRepository implements UserManagerInterface
{
    public function createUser(string $identifier, string $nickName, string $avatar): UserInterface
    {
        // 自定义用户创建逻辑
        return new CustomUser($identifier, $nickName, $avatar);
    }
}
```

## 实体类

Bundle 提供以下实体类：

- `User`：微信小程序用户实体
- `AuthLog`：认证日志记录
- `CodeSessionLog`：Code 换取 Session 日志
- `PhoneNumber`：用户手机号记录

## 事件

Bundle 会触发以下事件：

- `CodeToSessionRequestEvent`：Code 换取 Session 前触发
- `CodeToSessionResponseEvent`：成功创建 Session 后触发
- `GetPhoneNumberEvent`：获取手机号时触发
- `ChangePhoneNumberEvent`：更改手机号时触发

## 可用的 Procedure

可用的 JSON-RPC 过程：

- `WechatMiniProgramCodeToSession`：Code 换取 Session
- `GetCurrentWechatMiniProgramUser`：获取当前认证用户
- `UploadWechatMiniProgramPhoneNumber`：上传并绑定手机号
- `ReportWechatMiniProgramAuthorizeResult`：上报授权范围结果

## 安全性

### 数据保护

- 所有敏感数据都使用微信的加密标准进行加密
- 手机号存储时经过适当的验证和清理
- 用户令牌管理安全，具有适当的过期时间

### 最佳实践

- 在处理数据前始终验证微信签名
- 与微信 API 的所有通信都使用 HTTPS
- 为认证端点实现适当的限流
- 定期审计认证日志以发现可疑活动

### 安全注意事项

- 绝不以明文存储会话密钥
- 实现适当的会话管理，设置适当的超时时间
- 使用环境变量来存储敏感配置
- 定期更新依赖项以修补安全漏洞

## 错误处理

Bundle 提供自定义异常：

- `DecryptException`：数据解密失败
- `UserManagerNotAvailableException`：用户管理服务不可用
- `SystemUserNotFoundException`：系统用户未找到
- `UserRepositoryException`：用户仓储操作错误

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

## 许可证

MIT 许可证