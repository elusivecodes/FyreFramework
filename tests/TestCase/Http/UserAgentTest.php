<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\UserAgent;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class UserAgentTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(UserAgent::class)
        );
    }

    public function testGetAgentStringDesktop(): void
    {
        $this->assertSame(
            'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36',
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getAgentString()
        );
    }

    public function testGetAgentStringMobile(): void
    {
        $this->assertSame(
            'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getAgentString()
        );
    }

    public function testGetAgentStringRobot(): void
    {
        $this->assertSame(
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getAgentString()
        );
    }

    public function testGetBrowserDesktop(): void
    {
        $this->assertSame(
            'Chrome',
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getBrowser()
        );
    }

    public function testGetBrowserMobile(): void
    {
        $this->assertSame(
            'Safari',
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getBrowser()
        );
    }

    public function testGetBrowserRobot(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getBrowser()
        );
    }

    public function testGetMobileDesktop(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getMobile()
        );
    }

    public function testGetMobileMobile(): void
    {
        $this->assertSame(
            'Apple iPhone',
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getMobile()
        );
    }

    public function testGetMobileRobot(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getMobile()
        );
    }

    public function testGetPlatformDesktop(): void
    {
        $this->assertSame(
            'Windows 7',
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getPlatform()
        );
    }

    public function testGetPlatformMobile(): void
    {
        $this->assertSame(
            'iOS',
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getPlatform()
        );
    }

    public function testGetPlatformRobot(): void
    {
        $this->assertSame(
            'Unknown Platform',
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getPlatform()
        );
    }

    public function testGetRobotDesktop(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getRobot()
        );
    }

    public function testGetRobotMobile(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getRobot()
        );
    }

    public function testGetRobotRobot(): void
    {
        $this->assertSame(
            'Googlebot',
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getRobot()
        );
    }

    public function testGetVersionDesktop(): void
    {
        $this->assertSame(
            '47.0.2526.111',
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->getVersion()
        );
    }

    public function testGetVersionMobile(): void
    {
        $this->assertSame(
            '604.1',
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->getVersion()
        );
    }

    public function testGetVersionRobot(): void
    {
        $this->assertNull(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->getVersion()
        );
    }

    public function testIsBrowserDesktop(): void
    {
        $this->assertTrue(
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->isBrowser()
        );
    }

    public function testIsBrowserMobile(): void
    {
        $this->assertTrue(
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->isBrowser()
        );
    }

    public function testIsBrowserRobot(): void
    {
        $this->assertFalse(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->isBrowser()
        );
    }

    public function testIsMobileDesktop(): void
    {
        $this->assertFalse(
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36')
                ->isMobile()
        );
    }

    public function testIsMobileMobile(): void
    {
        $this->assertTrue(
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->isMobile()
        );
    }

    public function testIsMobileRobot(): void
    {
        $this->assertFalse(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->isMobile()
        );
    }

    public function testIsRobotDesktop(): void
    {
        $this->assertFalse(
            UserAgent::createFromString('Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36)')
                ->isRobot()
        );
    }

    public function testIsRobotMobile(): void
    {
        $this->assertFalse(
            UserAgent::createFromString('Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1')
                ->isRobot()
        );
    }

    public function testIsRobotRobot(): void
    {
        $this->assertTrue(
            UserAgent::createFromString('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)')
                ->isRobot()
        );
    }
}
