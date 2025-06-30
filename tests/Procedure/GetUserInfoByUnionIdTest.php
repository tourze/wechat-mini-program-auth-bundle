<?php

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\WechatMiniProgramUserContracts\UserLoaderInterface;
use WechatMiniProgramAuthBundle\Entity\User;
use WechatMiniProgramAuthBundle\Procedure\GetUserInfoByUnionId;
use WechatMiniProgramAuthBundle\Repository\PhoneNumberRepository;

class GetUserInfoByUnionIdTest extends TestCase
{
    private UserLoaderInterface $userLoader;
    private PhoneNumberRepository $phoneNumberRepository;
    private GetUserInfoByUnionId $procedure;

    protected function setUp(): void
    {
        $this->userLoader = $this->createMock(UserLoaderInterface::class);
        $this->phoneNumberRepository = $this->createMock(PhoneNumberRepository::class);
        $this->procedure = new GetUserInfoByUnionId($this->userLoader, $this->phoneNumberRepository);
    }

    public function testExecuteUserNotFound(): void
    {
        $this->procedure->unionId = 'test_union_id';

        $this->userLoader->expects(self::once())
            ->method('loadUserByUnionId')
            ->with('test_union_id')
            ->willReturn(null);

        $result = $this->procedure->execute();

        self::assertSame([], $result);
    }

    public function testExecuteSuccess(): void
    {
        $this->procedure->unionId = 'test_union_id';

        $user = new User();
        $user->setOpenId('test_open_id');
        $user->setUnionId('test_union_id');

        $this->userLoader->expects(self::once())
            ->method('loadUserByUnionId')
            ->with('test_union_id')
            ->willReturn($user);

        $result = $this->procedure->execute();

        self::assertSame([
            'open_id' => 'test_open_id',
            'union_id' => 'test_union_id',
            'phone' => '',
        ], $result);
    }
}