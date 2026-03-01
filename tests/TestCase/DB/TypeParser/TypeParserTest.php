<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Type;
use Fyre\DB\TypeParser;
use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeFractionalType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\JsonType;
use Fyre\DB\Types\SetType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;
use Fyre\Utility\DateTime\DateTime;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class TypeParserTest extends TestCase
{
    use BinaryTestTrait;
    use BooleanTestTrait;
    use DateTestTrait;
    use DateTimeFractionalTestTrait;
    use DateTimeTestTrait;
    use DateTimeTimeZoneTestTrait;
    use DecimalTestTrait;
    use EnumTestTrait;
    use FloatTestTrait;
    use IntegerTestTrait;
    use JsonTestTrait;
    use StringTestTrait;
    use TextTestTrait;
    use TimeTestTrait;

    protected TypeParser $type;

    public function testAlias(): void
    {
        $this->assertSame(
            $this->type->getType('boolean'),
            $this->type->getType('bool'),
        );

        $this->assertSame(
            $this->type->getType('integer'),
            $this->type->getType('int'),
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(TypeParser::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Type::class)
        );
    }

    public function testGetType(): void
    {
        $this->assertSame(
            BooleanType::class,
            $this->type->getType('boolean')
        );
    }

    public function testGetTypeDefault(): void
    {
        $this->assertSame(
            StringType::class,
            $this->type->getType('test')
        );
    }

    public function testGetTypeMap(): void
    {
        $this->assertSame(
            [
                'binary' => BinaryType::class,
                'boolean' => BooleanType::class,
                'date' => DateType::class,
                'datetime' => DateTimeType::class,
                'datetime-fractional' => DateTimeFractionalType::class,
                'datetime-timezone' => DateTimeTimeZoneType::class,
                'decimal' => DecimalType::class,
                'double' => DecimalType::class,
                'enum' => EnumType::class,
                'float' => FloatType::class,
                'integer' => IntegerType::class,
                'json' => JsonType::class,
                'set' => SetType::class,
                'string' => StringType::class,
                'text' => TextType::class,
                'time' => TimeType::class,
            ],
            $this->type->getTypeMap()
        );
    }

    public function testMap(): void
    {
        $this->assertSame(
            $this->type,
            $this->type->map('boolean', IntegerType::class)
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $this->type->use('boolean')
        );
    }

    public function testUse(): void
    {
        $this->assertSame(
            $this->type->use('boolean'),
            $this->type->use('boolean')
        );
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        DateTime::setDefaultLocale('en');
        DateTime::setDefaultTimeZone('UTC');
    }

    #[Override]
    protected function setUp(): void
    {
        $this->type = new Container()->use(TypeParser::class);
    }
}
