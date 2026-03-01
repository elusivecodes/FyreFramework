<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use InvalidArgumentException;

trait LocaleTestTrait
{
    public function testGetDefaultLocale(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertSame(
            'en',
            $request->getDefaultLocale()
        );
    }

    public function testGetLocale(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertSame(
            'en',
            $request->getLocale()
        );
    }

    public function testWithLocale(): void
    {
        $this->config->set('App.supportedLocales', ['en-US']);

        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withLocale('en-US');

        $this->assertSame(
            'en',
            $request1->getLocale()
        );

        $this->assertSame(
            'en-US',
            $request2->getLocale()
        );
    }

    public function testWithLocaleInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Locale `ru` is not supported.');

        $request = new ServerRequest($this->config, $this->type);

        $request->withLocale('ru');
    }
}
