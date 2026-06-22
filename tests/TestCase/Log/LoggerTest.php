<?php
declare(strict_types=1);

namespace Tests\TestCase\Log;

use ArrayIterator;
use ArrayObject;
use Fyre\Log\Handlers\ArrayLogger;
use JsonSerializable;
use Override;
use PHPUnit\Framework\TestCase;
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

    public function testGetConfig(): void
    {
        $logger = new ArrayLogger([
            'dateFormat' => 'U',
            'levels' => 'debug',
            'scopes' => 'api',
        ]);

        $this->assertSame(
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

    public function testInterpolateContextValues(): void
    {
        $jsonFlags = JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE;
        $arrayObject = new ArrayObject(['test' => 3]);
        $serializable = new ArrayIterator(['test' => 3]);
        $stringable = new class () implements Stringable
        {
            #[Override]
            public function __toString(): string
            {
                return 'stringable';
            }
        };
        $jsonSerializable = new class () implements JsonSerializable
        {
            /**
             * @return array<string, int>
             */
            #[Override]
            public function jsonSerialize(): array
            {
                return ['test' => 2];
            }
        };
        $arrayable = new class ()
        {
            /**
             * @return array<string, int>
             */
            public function toArray(): array
            {
                return ['test' => 4];
            }
        };
        $debuggable = new class ()
        {
            /**
             * @return array<string, int>
             */
            public function __debugInfo(): array
            {
                return ['test' => 5];
            }
        };
        $unhandled = new class () {};

        $this->logger->log('debug', '{array} {json} {array_object} {serializable} {stringable} {arrayable} {debuggable} {unhandled}', [
            'array' => ['test' => 1],
            'json' => $jsonSerializable,
            'array_object' => $arrayObject,
            'serializable' => $serializable,
            'stringable' => $stringable,
            'arrayable' => $arrayable,
            'debuggable' => $debuggable,
            'unhandled' => $unhandled,
        ]);

        $this->assertSame(
            '[DEBUG] '.
            json_encode(['test' => 1], $jsonFlags).' '.
            json_encode(['test' => 2], $jsonFlags).' '.
            json_encode($arrayObject->getArrayCopy(), $jsonFlags).' '.
            serialize($serializable).' '.
            'stringable '.
            json_encode(['test' => 4], $jsonFlags).' '.
            json_encode(['test' => 5], $jsonFlags).' '.
            '[unhandled type '.get_debug_type($unhandled).']',
            $this->logger->read()[0] ?? ''
        );
    }

    public function testInterpolateSessionAndEscapedPlaceholders(): void
    {
        $_SESSION = ['user' => 1];

        $this->logger->log('debug', '{session_vars} \{session_vars} {missing}');

        $this->assertSame(
            '[DEBUG] '.
            json_encode($_SESSION, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).' \\'.
            json_encode($_SESSION, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE).' {missing}',
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
