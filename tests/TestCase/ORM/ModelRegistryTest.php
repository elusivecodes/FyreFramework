<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\ORM\Relationship;
use Fyre\ORM\Result;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Mock\Entities\User;
use Tests\Mock\Models\ORM\ItemsModel;
use Tests\Mock\Models\ORM\UsersModel;
use Tests\TestCase\ORM\Mysql\MysqlConnectionTrait;

use function class_uses;

final class ModelRegistryTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testBuild(): void
    {
        $model = $this->modelRegistry->build('Items');

        $this->assertInstanceOf(
            ItemsModel::class,
            $model
        );

        $this->assertSame(
            'Items',
            $model->getAlias()
        );

        $this->assertFalse(
            $this->modelRegistry->isLoaded('Items')
        );
    }

    public function testBuildDefault(): void
    {
        $model = $this->modelRegistry->build('Invalid');

        $this->assertInstanceOf(
            Model::class,
            $model
        );

        $this->assertSame(
            'Invalid',
            $model->getClassAlias()
        );
    }

    public function testClear(): void
    {
        $this->modelRegistry->use('Items');

        $this->modelRegistry->clear();

        $this->assertArraysAreIdentical(
            [],
            $this->modelRegistry->getNamespaces()
        );

        $this->assertFalse(
            $this->modelRegistry->isLoaded('Items')
        );
    }

    public function testCreateDefaultModel(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->setDefaultModelClass(UsersModel::class)
        );

        $this->assertInstanceOf(
            UsersModel::class,
            $this->modelRegistry->createDefaultModel()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ModelRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Model::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Relationship::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Result::class)
        );
    }

    public function testGetDefaultModelClass(): void
    {
        $this->assertSame(
            Model::class,
            $this->modelRegistry->getDefaultModelClass()
        );
    }

    public function testGetEntityClass(): void
    {
        $model = $this->modelRegistry->use('Members', 'Users');

        $this->assertSame(
            User::class,
            $model->getEntityClass()
        );

        $this->assertSame(
            'Members',
            $model->newEmptyEntity()->getModelAlias()
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertArraysAreIdentical(
            [
                'Tests\Mock\Models\ORM\\',
            ],
            $this->modelRegistry->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->modelRegistry->hasNamespace('Tests\Mock\Models\ORM')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->modelRegistry->hasNamespace('Tests\Invalid\Model')
        );
    }

    public function testIsLoaded(): void
    {
        $this->modelRegistry->use('Items');

        $this->assertTrue(
            $this->modelRegistry->isLoaded('Items')
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->modelRegistry->isLoaded('Invalid')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Model::class)
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->removeNamespace('Tests\Mock\Models\ORM')
        );

        $this->assertFalse(
            $this->modelRegistry->hasNamespace('Tests\Mock\Models\ORM')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->removeNamespace('Tests\Invalid\Model')
        );
    }

    public function testSaveEntityFromMatchingModelClass(): void
    {
        $Members = $this->modelRegistry->use('Members', 'Users');
        $Users = $this->modelRegistry->use('Users');

        $user = $Members->newEmptyEntity()
            ->setNew(false);

        $this->assertTrue(
            $Users->save($user)
        );
    }

    public function testSaveInvalidEntityClass(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessageIs('Model `Users` requires an entity of type `Tests\Mock\Entities\User`, `Tests\Mock\Entities\Post` given.');

        $Users = $this->modelRegistry->createDefaultModel()
            ->setClassAlias('Users');
        $post = $this->modelRegistry->use('Posts')
            ->newEmptyEntity()
            ->setNew(false);

        $Users->saveMany([$post]);
    }

    public function testSetDefaultModelClass(): void
    {
        $this->modelRegistry->setDefaultModelClass(UsersModel::class);

        $this->assertSame(
            UsersModel::class,
            $this->modelRegistry->getDefaultModelClass()
        );
    }

    public function testUnload(): void
    {
        $this->modelRegistry->use('Items');

        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->unload('Items')
        );

        $this->assertFalse(
            $this->modelRegistry->isLoaded('Items')
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->unload('Invalid')
        );
    }

    public function testUse(): void
    {
        $model = $this->modelRegistry->use('Items');

        $this->assertInstanceOf(
            ItemsModel::class,
            $model
        );

        $this->assertTrue(
            $this->modelRegistry->isLoaded('Items')
        );
    }

    public function testUseClassAlias(): void
    {
        $model = $this->modelRegistry->use('Members', 'Users');

        $this->assertInstanceOf(
            UsersModel::class,
            $model
        );

        $this->assertSame(
            'Members',
            $model->getAlias()
        );

        $this->assertSame(
            'Users',
            $model->getClassAlias()
        );
    }

    public function testUseInvalidClassAlias(): void
    {
        $this->modelRegistry->use('Members', 'Users');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Model alias `Members` is already used by another class.');

        $this->modelRegistry->use('Members', 'Items');
    }

    public function testUseShared(): void
    {
        $this->assertSame(
            $this->modelRegistry->use('Items'),
            $this->modelRegistry->use('Items')
        );
    }

    public function testUseUnloadRebuilds(): void
    {
        $model = $this->modelRegistry->use('Items');

        $this->modelRegistry->unload('Items');

        $this->assertNotSame(
            $model,
            $this->modelRegistry->use('Items')
        );
    }
}
