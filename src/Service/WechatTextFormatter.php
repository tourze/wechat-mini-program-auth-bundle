<?php

namespace WechatMiniProgramAuthBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TextManageBundle\Service\TextFormatter;
use WechatMiniProgramAuthBundle\Repository\UserRepository;

#[AsDecorator(decorates: TextFormatter::class)]
class WechatTextFormatter implements TextFormatter
{
    public function __construct(
        #[AutowireDecorated] private readonly TextFormatter $inner,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function formatText(string $text, array $params = []): string
    {
        $text = $this->inner->formatText($text, $params);

        foreach ($params as $value) {
            if ($value instanceof UserInterface) {
                $wechatUser = $this->userRepository->transformToWechatUser($value);
                if ($wechatUser === null) {
                    continue;
                }

                $text = str_replace('{wechatMiniProgram:openId}', $wechatUser->getOpenId(), $text);
                $text = str_replace('{wechatMiniProgram:unionId}', $wechatUser->getUnionId(), $text);
            }
        }

        return $text;
    }
}
