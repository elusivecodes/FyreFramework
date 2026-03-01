<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\DB\TypeParser;
use Fyre\Form\Rule;
use Fyre\Form\Validator;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

use const ROOT;

final class RulesTest extends TestCase
{
    use AlphaNumericTestTrait;
    use AlphaTestTrait;
    use AsciiTestTrait;
    use BetweenTestTrait;
    use BooleanTestTrait;
    use DateTestTrait;
    use DateTimeTestTrait;
    use DecimalTestTrait;
    use DiffersTestTrait;
    use EmailTestTrait;
    use EmptyTestTrait;
    use EqualsTestTrait;
    use ExactLengthTestTrait;
    use GreaterThanOrEqualsTestTrait;
    use GreaterThanTestTrait;
    use IntegerTestTrait;
    use InTestTrait;
    use IpTestTrait;
    use Ipv4TestTrait;
    use Ipv6TestTrait;
    use LessThanOrEqualsTestTrait;
    use LessThanTestTrait;
    use MatchesTestTrait;
    use MaxLengthTestTrait;
    use MinLengthTestTrait;
    use NaturalNumberTestTrait;
    use NotEmptyTestTrait;
    use RegexTestTrait;
    use RequiredTestTrait;
    use RequirePresenceTestTrait;
    use TimeTestTrait;
    use UrlTestTrait;

    protected Validator $validator;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Rule::class)
        );
    }

    public function testGetArguments(): void
    {
        $rule = Rule::between(5, 10);

        $this->assertSame(
            [5, 10],
            $rule->getArguments()
        );
    }

    public function testGetName(): void
    {
        $rule = Rule::alpha();

        $this->assertSame(
            'alpha',
            $rule->getName()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            StaticMacroTrait::class,
            class_uses(Rule::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(Lang::class);
        $container->singleton(Config::class);

        $container->use(Config::class)->set('App.locale', 'en');

        $container->use(Lang::class)
            ->addPath(Path::join(ROOT, 'lang'));

        $this->validator = $container->use(Validator::class);
    }
}
