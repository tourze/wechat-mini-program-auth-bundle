# wechat-mini-program-auth-bundle

[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://www.php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Symfony](https://img.shields.io/badge/symfony-%5E6.4-blue.svg)](https://symfony.com)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/test.yml?branch=master)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo/master)](https://codecov.io/gh/tourze/php-monorepo)

[English](README.md) | [中文](README.zh-CN.md)

WeChat Mini Program Authentication Bundle for Symfony

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [1. Code to Session](#1-code-to-session)
  - [2. Get Current User](#2-get-current-user)
  - [3. Upload Phone Number](#3-upload-phone-number)
- [Advanced Usage](#advanced-usage)
  - [Custom Event Handlers](#custom-event-handlers)
  - [Integration with User Management Systems](#integration-with-user-management-systems)
- [Entities](#entities)
- [Events](#events)
- [Procedures](#procedures)
- [Security](#security)
- [Error Handling](#error-handling)
- [Requirements](#requirements)
- [License](#license)

## Features

- WeChat Mini Program user authentication
- Code to session conversion
- User profile management  
- Phone number binding and verification
- Data encryption/decryption service
- Event-driven architecture for customization
- Comprehensive logging for debugging

## Installation

```bash
composer require tourze/wechat-mini-program-auth-bundle
```

## Configuration

### 1. Register the Bundle

Register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    WechatMiniProgramAuthBundle\WechatMiniProgramAuthBundle::class => ['all' => true],
];
```

### 2. Configure Services

The bundle provides auto-configuration for all services. Key services include:

- `EncryptService`: Handles WeChat data decryption
- `WechatTextFormatter`: Formats WeChat-specific text
- `UserService`: Manages WeChat Mini Program user creation and persistence
- `UserTransformService`: Transforms between WeChat users and system users

## Usage

### 1. Code to Session

Convert WeChat authorization code to session:

```php
use WechatMiniProgramAuthBundle\Procedure\WechatMiniProgramCodeToSession;

// Via JSON-RPC
$result = $procedure->execute([
    'code' => 'authorization_code',
    'rawData' => '{"nickName":"User",...}',
    'signature' => 'signature_string',
    'encryptedData' => 'encrypted_data',
    'iv' => 'initialization_vector'
]);
```

### 2. Get Current User

Get the currently authenticated WeChat Mini Program user:

```php
use WechatMiniProgramAuthBundle\Procedure\GetCurrentWechatMiniProgramUser;

$user = $procedure->execute();
```

### 3. Upload Phone Number

Upload and bind user phone number:

```php
use WechatMiniProgramAuthBundle\Procedure\UploadWechatMiniProgramPhoneNumber;

$result = $procedure->execute([
    'encryptedData' => 'encrypted_phone_data',
    'iv' => 'initialization_vector'
]);
```

## Advanced Usage

### Custom Event Handlers

Listen to authentication events:

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
        // Custom logic after user authentication
        $user = $event->getWechatUser();
        // ...
    }
}
```

### Integration with User Management Systems

Extend user repository for custom user creation:

```php
use WechatMiniProgramAuthBundle\Repository\UserRepository;
use Tourze\UserServiceContracts\UserManagerInterface;

class CustomUserRepository extends UserRepository implements UserManagerInterface
{
    public function createUser(string $identifier, string $nickName, string $avatar): UserInterface
    {
        // Custom user creation logic
        return new CustomUser($identifier, $nickName, $avatar);
    }
}
```

## Entities

The bundle provides the following entities:

- `User`: WeChat Mini Program user entity
- `AuthLog`: Authentication log records
- `CodeSessionLog`: Code to session conversion logs
- `PhoneNumber`: User phone number records

## Events

The bundle dispatches the following events:

- `CodeToSessionRequestEvent`: Before code to session conversion
- `CodeToSessionResponseEvent`: After successful session creation
- `GetPhoneNumberEvent`: When retrieving phone number
- `ChangePhoneNumberEvent`: When changing phone number

## Procedures

Available JSON-RPC procedures:

- `WechatMiniProgramCodeToSession`: Convert authorization code to session
- `GetCurrentWechatMiniProgramUser`: Get current authenticated user
- `UploadWechatMiniProgramPhoneNumber`: Upload and bind phone number
- `ReportWechatMiniProgramAuthorizeResult`: Report authorization scope results

## Security

### Data Protection

- All sensitive data is encrypted using WeChat's encryption standards
- Phone numbers are stored with proper validation and sanitization
- User tokens are managed securely with proper expiration

### Best Practices

- Always validate WeChat signatures before processing data
- Use HTTPS for all communications with WeChat APIs
- Implement proper rate limiting for authentication endpoints
- Regularly audit authentication logs for suspicious activity

### Security Considerations

- Never store session keys in plain text
- Implement proper session management with appropriate timeouts
- Use environment variables for sensitive configuration
- Regularly update dependencies to patch security vulnerabilities

## Error Handling

The bundle provides custom exceptions:

- `DecryptException`: Data decryption failures
- `UserManagerNotAvailableException`: User manager service unavailable
- `SystemUserNotFoundException`: System user not found
- `UserRepositoryException`: User repository operation errors

## Requirements

- PHP 8.1+
- Symfony 6.4+
- Doctrine ORM 3.0+

## License

MIT License