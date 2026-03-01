<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\TypeParser;
use Fyre\Http\Request;
use Fyre\Http\ServerRequest;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ServerRequestTest extends TestCase
{
    use CookieTestTrait;
    use DataTestTrait;
    use EnvTestTrait;
    use LocaleTestTrait;
    use NegotiateTestTrait;
    use QueryTestTrait;
    use ServerTestTrait;
    use UploadedFileTestTrait;
    use UriTestTrait;
    use UserAgentTestTrait;

    protected Config $config;

    protected TypeParser $type;

    public function testIsAjax(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertFalse(
            $request->isAjax()
        );
    }

    public function testIsAjaxTrue(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTP_X_REQUESTED_WITH' => 'XmlHttpRequest',
            ],
        ]);

        $this->assertTrue(
            $request->isAjax()
        );
    }

    public function testIsCli(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertTrue(
            $request->isCli()
        );
    }

    public function testIsSecure(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertFalse(
            $request->isSecure()
        );
    }

    public function testIsSecureForwardedProto(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }

    public function testIsSecureFrontEndHttps(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTP_FRONT_END_HTTPS' => 'ON',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }

    public function testIsSecureHttps(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTPS' => 'ON',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(ServerRequest::class)
        );
    }

    public function testRequest(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertInstanceOf(
            Request::class,
            $request
        );
    }

    public function testWithAttribute(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withAttribute('test', 'value');

        $this->assertEmpty(
            $request1->getAttributes()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request2->getAttributes()
        );

        $this->assertSame(
            'value',
            $request2->getAttribute('test')
        );
    }

    public function testWithoutAttribute(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withAttribute('test', 'value');
        $request3 = $request2->withoutAttribute('test');

        $this->assertNull(
            $request1->getAttribute('test')
        );

        $this->assertSame(
            'value',
            $request2->getAttribute('test')
        );

        $this->assertNull(
            $request3->getAttribute('test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->config = new Config();
        $this->config->set('App.defaultLocale', 'en');

        $this->type = new Container()->use(TypeParser::class);
    }
}
