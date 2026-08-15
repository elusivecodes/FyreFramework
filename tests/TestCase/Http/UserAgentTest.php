<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\UserAgent;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class UserAgentTest extends TestCase
{
    /**
     * @return array<string, array{string, array<string, bool|string|null>}>
     */
    public static function userAgentProvider(): array
    {
        $desktop = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36';
        $mobile = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';
        $robot = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

        return [
            'desktop' => [$desktop, [
                'agent' => $desktop,
                'browser' => 'Chrome',
                'mobile' => null,
                'platform' => 'Windows 7',
                'robot' => null,
                'version' => '47.0.2526.111',
                'isBrowser' => true,
                'isMobile' => false,
                'isRobot' => false,
            ]],
            'mobile' => [$mobile, [
                'agent' => $mobile,
                'browser' => 'Safari',
                'mobile' => 'Apple iPhone',
                'platform' => 'iOS',
                'robot' => null,
                'version' => '604.1',
                'isBrowser' => true,
                'isMobile' => true,
                'isRobot' => false,
            ]],
            'robot' => [$robot, [
                'agent' => $robot,
                'browser' => null,
                'mobile' => null,
                'platform' => 'Unknown Platform',
                'robot' => 'Googlebot',
                'version' => null,
                'isBrowser' => false,
                'isMobile' => false,
                'isRobot' => true,
            ]],
        ];
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(UserAgent::class)
        );
    }

    /**
     * @param array<string, bool|string|null> $expected
     */
    #[DataProvider('userAgentProvider')]
    public function testUserAgent(string $agent, array $expected): void
    {
        $userAgent = UserAgent::createFromString($agent);

        $this->assertSame(
            $expected,
            [
                'agent' => $userAgent->getAgentString(),
                'browser' => $userAgent->getBrowser(),
                'mobile' => $userAgent->getMobile(),
                'platform' => $userAgent->getPlatform(),
                'robot' => $userAgent->getRobot(),
                'version' => $userAgent->getVersion(),
                'isBrowser' => $userAgent->isBrowser(),
                'isMobile' => $userAgent->isMobile(),
                'isRobot' => $userAgent->isRobot(),
            ]
        );
    }
}
