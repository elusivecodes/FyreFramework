<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use ArrayIterator;
use ArrayObject;
use Closure;
use Fyre\Log\Handlers\ArrayLogger;
use JsonSerializable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Stringable;

use function array_key_exists;
use function get_debug_type;
use function json_encode;
use function serialize;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

final class LoggerTest extends TestCase
{
    protected bool $hadSession = false;

    protected ArrayLogger $logger;

    protected mixed $session = null;

    /**
     * @return array<string, array{Closure(): mixed, string}>
     */
    public static function interpolateContextProvider(): array
    {
        return [
            'array' => [
                static fn(): array => ['test' => 1],
                '{"test":1}',
            ],
            'json serializable' => [
                static fn(): JsonSerializable => new class () implements JsonSerializable
                {
                    /**
                     * @return array<string, int>
                     */
                    #[Override]
                    public function jsonSerialize(): array
                    {
                        return ['test' => 2];
                    }
                },
                '{"test":2}',
            ],
            'array object' => [
                static fn(): ArrayObject => new ArrayObject(['test' => 3]),
                '{"test":3}',
            ],
            'stringable' => [
                static fn(): Stringable => new class () implements Stringable
                {
                    #[Override]
                    public function __toString(): string
                    {
                        return 'stringable';
                    }
                },
                'stringable',
            ],
            'arrayable' => [
                static fn(): object => new class ()
                {
                    /**
                     * @return array<string, int>
                     */
                    public function toArray(): array
                    {
                        return ['test' => 4];
                    }
                },
                '{"test":4}',
            ],
            'debuggable' => [
                static fn(): object => new class ()
                {
                    /**
                     * @return array<string, int>
                     */
                    public function __debugInfo(): array
                    {
                        return ['test' => 5];
                    }
                },
                '{"test":5}',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function interpolateGlobalsProvider(): array
    {
        return [
            'GET' => ['get_vars', '_GET'],
            'POST' => ['post_vars', '_POST'],
            'SERVER' => ['server_vars', '_SERVER'],
        ];
    }

    public function testGetConfig(): void
    {
        $logger = new ArrayLogger([
            'dateFormat' => 'U',
            'levels' => 'debug',
            'scopes' => 'api',
        ]);

        $this->assertArraysAreIdentical(
            [
                'dateFormat' => 'U',
                'levels' => ['debug'],
                'scopes' => ['api'],
            ],
            $logger->getConfig()
        );
    }

    public function testInterpolateBacktrace(): void
    {
        $this->logger->log('debug', '{backtrace}');

        $this->assertStringContainsString(
            'testInterpolateBacktrace',
            $this->logger->read()[0] ?? ''
        );
    }

    /**
     * @param Closure(): mixed $value
     */
    #[DataProvider('interpolateContextProvider')]
    public function testInterpolateContextValue(Closure $value, string $expected): void
    {
        $this->logger->log('debug', '{value}', [
            'value' => $value(),
        ]);

        $this->assertSame(
            '[DEBUG] '.$expected,
            $this->logger->read()[0] ?? ''
        );
    }

    #[DataProvider('interpolateGlobalsProvider')]
    public function testInterpolateGlobals(string $placeholder, string $global): void
    {
        $original = $GLOBALS[$global];

        try {
            $GLOBALS[$global] = [$placeholder => 'test'];

            $this->logger->log('debug', '{'.$placeholder.'}');

            $this->assertSame(
                '[DEBUG] {"'.$placeholder.'":"test"}',
                $this->logger->read()[0] ?? ''
            );
        } finally {
            $GLOBALS[$global] = $original;
        }
    }

    public function testInterpolateInvalidContextValue(): void
    {
        $value = new class () implements JsonSerializable
        {
            #[Override]
            public function jsonSerialize(): mixed
            {
                throw new RuntimeException();
            }
        };

        $this->logger->log('debug', '{value}', [
            'value' => $value,
        ]);

        $this->assertSame(
            '[DEBUG] [unhandled type '.get_debug_type($value).']',
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateMissingSession(): void
    {
        unset($_SESSION);

        $this->logger->log('debug', '{session_vars}');

        $this->assertSame(
            '[DEBUG] []',
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateMultiplePlaceholders(): void
    {
        $this->logger->log('debug', '{first} {second} {first}', [
            'first' => 'one',
            'second' => 'two',
        ]);

        $this->assertSame(
            '[DEBUG] one two one',
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateSerializable(): void
    {
        $value = new ArrayIterator(['test' => 3]);
        $expected = serialize($value);

        $this->logger->log('debug', '{value}', [
            'value' => $value,
        ]);

        $this->assertSame(
            '[DEBUG] '.$expected,
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateSessionAndEscapedPlaceholders(): void
    {
        $_SESSION = ['user' => 1];

        $this->logger->log('debug', '{session_vars} \{session_vars} {missing}');

        $this->assertSame(
            '[DEBUG] '.
            json_encode($_SESSION, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).' {session_vars} {missing}',
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateUnhandled(): void
    {
        $value = new class () {};

        $this->logger->log('debug', '{value}', [
            'value' => $value,
        ]);

        $this->assertSame(
            '[DEBUG] [unhandled type '.get_debug_type($value).']',
            $this->logger->read()[0] ?? ''
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->hadSession = array_key_exists('_SESSION', $GLOBALS);
        $this->session = $GLOBALS['_SESSION'] ?? null;
        $this->logger = new ArrayLogger();
    }

    #[Override]
    protected function tearDown(): void
    {
        if ($this->hadSession) {
            $_SESSION = $this->session;
        } else {
            unset($_SESSION);
        }
    }
}
