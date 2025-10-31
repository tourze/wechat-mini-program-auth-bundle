<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TextManageBundle\Service\TextFormatter;

#[AsDecorator(decorates: TextFormatter::class)]
class WechatTextFormatter implements TextFormatter
{
    public function __construct(
        #[AutowireDecorated] private readonly TextFormatter $inner,
        private readonly UserTransformService $userTransformService,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function formatText(string $text, array $params = []): string
    {
        /** @var array<string, mixed> $stringKeyedParams */
        $stringKeyedParams = $params;
        $text = $this->inner->formatText($text, $stringKeyedParams);

        foreach ($params as $value) {
            if ($value instanceof UserInterface) {
                $wechatUser = $this->userTransformService->transformToWechatUser($value);
                if (null === $wechatUser) {
                    continue;
                }

                $text = str_replace('{wechatMiniProgram:openId}', $wechatUser->getOpenId(), $text);
                $text = str_replace('{wechatMiniProgram:unionId}', $wechatUser->getUnionId() ?? '', $text);
            }
        }

        return $text;
    }
}
