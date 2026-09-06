<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Client;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Client\Response;
use Fyre\Http\Cookie\Cookie;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ResponseTest extends TestCase
{
    /**
     * @return array<string, array{string, array<string, int|string>|bool|null}>
     */
    public static function jsonProvider(): array
    {
        return [
            'object with string value' => ['{"value":"1"}', ['value' => '1']],
            'object with integer value' => ['{"value":1}', ['value' => 1]],
            'null' => ['null', null],
            'boolean' => ['true', true],
        ];
    }

    public function testGetBody(): void
    {
        $response = new Response([
            'body' => 'This is a test.',
        ]);

        $this->assertSame(
            'This is a test.',
            $response->getBody()->getContents()
        );
    }

    public function testGetBodyEmpty(): void
    {
        $response = new Response();

        $this->assertSame(
            '',
            $response->getBody()->getContents()
        );
    }

    public function testGetCookie(): void
    {
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Path=/',
            ],
        ]);

        $cookie = $response->getCookie('test');

        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertSame('value', $cookie->getValue());
    }

    public function testGetCookies(): void
    {
        $response = new Response([
            'headers' => [
                'Set-Cookie' => 'test=value; Path=/',
            ],
        ]);

        $this->assertSame(
            [$response->getCookie('test')],
            $response->getCookies()
        );
    }

    /**
     * @param array<string, int|string>|bool|null $expected
     */
    #[DataProvider('jsonProvider')]
    public function testGetJson(string $body, array|bool|null $expected): void
    {
        $response = new Response([
            'body' => $body,
        ]);

        $this->assertSame(
            $expected,
            $response->getJson()
        );
    }

    public function testIsOk(): void
    {
        $response = new Response();

        $this->assertTrue(
            $response->isOk()
        );
    }

    public function testIsSuccess(): void
    {
        $response = new Response();

        $this->assertTrue(
            $response->isSuccess()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Response::class)
        );
    }
}
