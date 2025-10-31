<?php

declare(strict_types=1);

namespace WechatMiniProgramAuthBundle\Tests\Procedure;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;
use WechatMiniProgramAuthBundle\Entity\CodeSessionLog;
use WechatMiniProgramAuthBundle\Exception\TestApiCallException;
use WechatMiniProgramAuthBundle\Procedure\WechatMiniProgramCodeToSession;
use WechatMiniProgramAuthBundle\Request\CodeToSessionRequest;
use WechatMiniProgramBundle\Entity\Account;
use WechatMiniProgramBundle\Service\Client;

/**
 * @internal
 */
#[CoversClass(WechatMiniProgramCodeToSession::class)]
#[RunTestsInSeparateProcesses]
final class WechatMiniProgramCodeToSessionTest extends AbstractProcedureTestCase
{
    private WechatMiniProgramCodeToSession $procedure;

    protected function onSetUp(): void
    {
        // 在onSetUp中创建Mock客户端并替换服务
        $mockClient = $this->createMockClient();

        // 动态替换容器中的Client服务
        /** @var Container $container */
        /** @phpstan-ignore-next-line */
        $container = $this->getContainer();
        $container->set(Client::class, $mockClient);

        $this->procedure = self::getService(WechatMiniProgramCodeToSession::class);
    }

    public function testInstantiation(): void
    {
        $this->assertInstanceOf(WechatMiniProgramCodeToSession::class, $this->procedure);
    }

    /**
     * 创建模拟的微信客户端
     */
    private function createMockClient(): Client
    {
        $mockClient = $this->createMock(Client::class);

        $mockClient->method('request')
            ->willReturnCallback(function (CodeToSessionRequest $request) {
                return $this->handleMockRequest($request);
            })
        ;

        return $mockClient;
    }

    /**
     * 处理模拟请求
     *
     * @param CodeToSessionRequest $request
     *
     * @return array<string, mixed|null>
     */
    private function handleMockRequest(object $request): array
    {
        $jsCode = $request->getJsCode();
        if (!str_starts_with($jsCode, 'mock_')) {
            throw new TestApiCallException('Real API calls are not allowed in tests');
        }

        $jsonData = substr($jsCode, 5); // 移除 'mock_' 前缀
        $data = json_decode($jsonData, true);

        if (JSON_ERROR_NONE !== json_last_error() || !is_array($data)) {
            throw new TestApiCallException('Invalid mock data format');
        }

        /** @var array<string, mixed> $data */
        return $this->buildMockResponse($data);
    }

    /**
     * 构建模拟响应
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed|null>
     */
    private function buildMockResponse(array $data): array
    {
        $openid = is_string($data['openid'] ?? null) ? $data['openid'] : 'mock_openid';
        $unionid = is_string($data['unionid'] ?? null) ? $data['unionid'] : null;
        $sessionKey = is_string($data['session_key'] ?? null) ? $data['session_key'] : 'mock_session_key';

        // 对于空 session_key 的情况直接返回 null
        if (!isset($data['session_key']) || '' === $data['session_key']) {
            return [
                'openid' => $openid,
                'unionid' => $unionid,
                'session_key' => null,
            ];
        }

        return [
            'openid' => $openid,
            'unionid' => $unionid,
            'session_key' => $sessionKey,
        ];
    }

    public function testExecuteWithInvalidAccount(): void
    {
        // 确保数据库中没有Account记录
        self::getService(EntityManagerInterface::class)
            ->createQuery('DELETE FROM ' . Account::class)
            ->execute()
        ;

        $appId = 'nonexistent_app_id';
        $code = 'test_code';

        $request = new Request();
        $requestStack = self::getService(RequestStack::class);
        $requestStack->push($request);

        $this->procedure->appId = $appId;
        $this->procedure->code = $code;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('找不到小程序');

        $this->procedure->execute();
    }

    public function testExecuteWithInvalidSessionAndNoPreviousLog(): void
    {
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        // 使用模拟数据，避免调用真实微信API，使用空的 session_key 来触发错误流程
        $code = 'mock_{"openid":"test_openid_unique","unionid":"test_unionid","session_key":""}';

        $request = new Request();
        $requestStack = self::getService(RequestStack::class);
        $requestStack->push($request);

        $account = new Account();
        $account->setName('测试小程序');
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $account->setValid(true);
        $this->persistAndFlush($account);

        $this->procedure->appId = $appId;
        $this->procedure->code = $code;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序[1]');

        $this->procedure->execute();
    }

    public function testExecuteWithInvalidSessionAndOldPreviousLog(): void
    {
        $appId = 'test_app_id';
        $appSecret = 'test_app_secret';
        $code = 'mock_{"openid":"old_test_openid_unique","unionid":"test_unionid","session_key":""}';

        $request = new Request();
        $requestStack = self::getService(RequestStack::class);
        $requestStack->push($request);

        $account = new Account();
        $account->setName('测试小程序');
        $account->setAppId($appId);
        $account->setAppSecret($appSecret);
        $account->setValid(true);
        $this->persistAndFlush($account);

        $oldLog = new CodeSessionLog();
        $oldLog->setCreateTime(new \DateTimeImmutable('-30 minutes'));
        $oldLog->setCode($code);
        $oldLog->setAccount($account);
        $this->persistAndFlush($oldLog);

        $this->procedure->appId = $appId;
        $this->procedure->code = $code;

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信登录失败，请重新进入小程序[2]');

        $this->procedure->execute();
    }

    public function testGetLockResource(): void
    {
        $code = 'test_code';
        $params = new JsonRpcParams(['code' => $code]);

        $result = $this->procedure->getLockResource($params);

        self::assertEquals(['WechatMiniProgramCodeToSession' . $code], $result);
    }
}
