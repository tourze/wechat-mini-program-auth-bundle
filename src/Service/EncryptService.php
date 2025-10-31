<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Service;

use Tourze\WechatHelper\AES;
use WechatMiniProgramAuthBundle\Exception\DecryptException;

class EncryptService
{
    /**
     * 解密微信数据
     *
     * @return array<string, mixed>
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $encryptedData = base64_decode($encrypted, true);
        if (false === $encryptedData) {
            throw new DecryptException('Failed to decode encrypted data.');
        }

        $sessionKeyData = base64_decode($sessionKey, true);
        if (false === $sessionKeyData) {
            throw new DecryptException('Failed to decode session key.');
        }

        $ivData = base64_decode($iv, true);
        if (false === $ivData) {
            throw new DecryptException('Failed to decode IV.');
        }

        $decrypted = AES::decrypt(
            $encryptedData,
            $sessionKeyData,
            $ivData
        );

        /** @var array<mixed>|null $decoded */
        $decoded = json_decode($decrypted, true);

        if (!is_array($decoded)) {
            throw new DecryptException('The given payload is invalid.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
