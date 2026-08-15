<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Uri;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class UriTest extends TestCase
{
    use UriAttributesGetTestTrait;
    use UriAttributesWithTestTrait;
    use UriQueryTestTrait;
    use UriRelativeTestTrait;

    /**
     * @return array<string, array{string, string}>
     */
    public static function uriProvider(): array
    {
        return [
            'basic' => ['https://domain.com/', 'https://domain.com/'],
            'fragment' => ['https://domain.com/#test', 'https://domain.com/#test'],
            'password' => ['https://user:password@domain.com/', 'https://user:password@domain.com/'],
            'path' => ['https://domain.com/path/deep', 'https://domain.com/path/deep'],
            'port' => ['https://domain.com:3000/', 'https://domain.com:3000/'],
            'query' => ['https://domain.com/?test=1', 'https://domain.com/?test=1'],
            'username' => ['https://user@domain.com/', 'https://user@domain.com/'],
            'without host' => ['/path/deep', '/path/deep'],
            'with trailing slash' => ['/path/deep/', '/path/deep/'],
        ];
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Uri::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Uri::class)
        );
    }

    #[DataProvider('uriProvider')]
    public function testUri(string $value, string $expected): void
    {
        $this->assertSame(
            $expected,
            Uri::createFromString($value)->getUri()
        );
    }
}
