<?php

namespace WechatMiniProgramAuthBundle\Service;

use Tourze\WechatHelper\AES;
use WechatMiniProgramAuthBundle\Exception\DecryptException;

class EncryptService
{
    /**
     * Decrypt data.
     */
    public function decryptData(string $sessionKey, string $iv, string $encrypted): array
    {
        $decrypted = AES::decrypt(
            base64_decode($encrypted, false),
            base64_decode($sessionKey, false),
            base64_decode($iv, false)
        );

        $decrypted = json_decode($decrypted, true);

        if (!$decrypted) {
            throw new DecryptException('The given payload is invalid.');
        }

        return $decrypted;
    }
}
