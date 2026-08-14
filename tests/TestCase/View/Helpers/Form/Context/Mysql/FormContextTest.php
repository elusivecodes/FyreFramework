<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Mysql;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
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
use Tests\TestCase\View\Helpers\Form\Context\Shared\ConnectionTrait;

use function getenv;

final class FormContextTest extends TestCase
{
    use BigIntTestTrait;
    use BlobTestTrait;
    use BooleanTestTrait;
    use CharTestTrait;
    use ConnectionTrait;
    use DateTestTrait;
    use DateTimeTestTrait;
    use DecimalTestTrait;
    use DoubleTestTrait;
    use EnumClassTestTrait;
    use EnumTestTrait;
    use FloatTestTrait;
    use IntTestTrait;
    use LongBlobTestTrait;
    use LongTextTestTrait;
    use MediumBlobTestTrait;
    use MediumIntTestTrait;
    use MediumTextTestTrait;
    use PrimaryKeyTestTrait;
    use RelationshipTestTrait;
    use SetTestTrait;
    use SmallIntTestTrait;
    use TextTestTrait;
    use TimeTestTrait;
    use TinyBlobTestTrait;
    use TinyIntTestTrait;
    use TinyTextTestTrait;
    use VarcharTestTrait;

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
                    'className' => MysqlConnection::class,
                    'host' => getenv('MYSQL_HOST'),
                    'username' => getenv('MYSQL_USERNAME'),
                    'password' => getenv('MYSQL_PASSWORD'),
                    'database' => getenv('MYSQL_DATABASE'),
                    'port' => getenv('MYSQL_PORT'),
                    'collation' => 'utf8mb4_unicode_ci',
                    'charset' => 'utf8mb4',
                    'compress' => true,
                    'persist' => true,
                ],
            ]);

        return $container;
    }
}
