<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Sqlite;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Fyre\Utility\Inflector;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\View\Helpers\Form\Context\Traits\ConnectionTrait;

final class FormContextTest extends TestCase
{
    use BigIntTestTrait;
    use BlobTestTrait;
    use BooleanTestTrait;
    use ConnectionTrait;
    use DateTestTrait;
    use DateTimeTestTrait;
    use DoubleTestTrait;
    use EnumClassTestTrait;
    use IntegerTestTrait;
    use NumericTestTrait;
    use RealTestTrait;
    use SmallIntTestTrait;
    use TextTestTrait;
    use TimeTestTrait;

    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(CellRegistry::class);
        $container->singleton(HtmlHelper::class);
        $container->singleton(FormBuilder::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(TypeParser::class);
        $container->singleton(Inflector::class);
        $container->singleton(SchemaRegistry::class);
        $container->singleton(ModelRegistry::class);
        $container->singleton(EntityLocator::class);

        $container->use(Config::class)
            ->set('Database', [
                'default' => [
                    'className' => SqliteConnection::class,
                    'persist' => true,
                ],
            ]);

        return $container;
    }
}
