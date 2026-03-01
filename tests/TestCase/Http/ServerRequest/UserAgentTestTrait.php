<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Http\UserAgent;

trait UserAgentTestTrait
{
    public function testServerUserAgent(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            ],
        ]);

        $this->assertSame(
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            $request->getUserAgent()->getAgentString()
        );
    }

    public function testUserAgent(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertInstanceOf(
            UserAgent::class,
            $request->getUserAgent()
        );
    }
}
