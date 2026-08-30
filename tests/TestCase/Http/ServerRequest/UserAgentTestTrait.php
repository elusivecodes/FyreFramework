<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Http\UserAgent;

trait UserAgentTestTrait
{
    public function testUserAgent(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertInstanceOf(
            UserAgent::class,
            $request->getUserAgent()
        );
    }

    public function testUserAgentString(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            ],
        ]);

        $this->assertSame(
            'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            $request->getUserAgent()->getAgentString()
        );
    }
}
