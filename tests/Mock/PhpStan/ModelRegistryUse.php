<?php
declare(strict_types=1);

namespace Tests\Mock\PhpStan;

use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Tests\Mock\Models\ItemsModel as OverrideItemsModel;
use Tests\Mock\Models\ORM\ItemsModel;

use function PHPStan\Testing\assertType;

function modelRegistryUse(ModelRegistry $modelRegistry): void
{
    assertType(ItemsModel::class, $modelRegistry->use('Items'));
    assertType(ItemsModel::class, $modelRegistry->use('Alias', 'Items'));
}

function modelRegistryUseFallback(ModelRegistry $modelRegistry, string $alias): void
{
    assertType(Model::class, $modelRegistry->use('Missing'));
    assertType(Model::class, $modelRegistry->use('Invalid'));
    assertType(Model::class, $modelRegistry->use($alias));
}

final class ModelRegistryOverride
{
    public function test(ModelRegistry $modelRegistry): void
    {
        assertType(OverrideItemsModel::class, $modelRegistry->use('Items'));
    }
}
