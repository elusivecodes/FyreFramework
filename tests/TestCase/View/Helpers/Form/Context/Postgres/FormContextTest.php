<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers\Form\Context\Postgres;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Form\Validator;
use Fyre\Http\ServerRequest;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\Utility\FormBuilder;
use Fyre\Utility\HtmlHelper;
use Fyre\Utility\Inflector;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;

use function getenv;

final class FormContextTest extends TestCase
{
    use BigIntTestTrait;
    use BooleanTestTrait;
    use ByteaTestTrait;
    use CharacterTestTrait;
    use CharacterVaryingTestTrait;
    use DateTestTrait;
    use DoublePrecisionTestTrait;
    use EnumClassTestTrait;
    use IntegerTestTrait;
    use NumericTestTrait;
    use PrimaryKeyTestTrait;
    use RealTestTrait;
    use RelationshipTestTrait;
    use SmallIntTestTrait;
    use TextTestTrait;
    use TimestampTestTrait;
    use TimeTestTrait;

    protected Connection $db;

    protected Model $model;

    protected Validator $validator;

    protected View $view;

    #[Override]
    protected function setUp(): void
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
        $container->singleton(ConnectionManager::class);
        $container->singleton(SchemaRegistry::class);
        $container->singleton(ModelRegistry::class);
        $container->singleton(EntityLocator::class);

        $container->use(Config::class)
            ->set('Database', [
                'default' => [
                    'className' => PostgresConnection::class,
                    'host' => getenv('POSTGRES_HOST'),
                    'username' => getenv('POSTGRES_USERNAME'),
                    'password' => getenv('POSTGRES_PASSWORD'),
                    'database' => getenv('POSTGRES_DATABASE'),
                    'port' => getenv('POSTGRES_PORT'),
                    'charset' => 'utf8',
                    'persist' => true,
                ],
            ]);

        $this->db = $container->use(ConnectionManager::class)->use();

        $this->db->query('DROP TABLE IF EXISTS contexts');
        $this->db->query('DROP TABLE IF EXISTS parents');
        $this->db->query('DROP TABLE IF EXISTS children');
        $this->db->query('DROP TABLE IF EXISTS contexts_children');

        $this->model = $container->use(ModelRegistry::class)->use('Contexts');
        $this->validator = $container->build(Validator::class);

        $this->model->setValidator($this->validator);

        $request = $container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $this->view = $container->build(View::class, ['request' => $request]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS contexts');
        $this->db->query('DROP TABLE IF EXISTS parents');
        $this->db->query('DROP TABLE IF EXISTS children');
        $this->db->query('DROP TABLE IF EXISTS contexts_children');
    }
}
