<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Make;

use Fyre\Core\Make\Traits\UseStatementBuilderTrait;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function implode;

use const PHP_EOL;

final class UseStatementBuilderTest extends TestCase
{
    use UseStatementBuilderTrait;

    public function testBuild(): void
    {
        $this->assertSame(
            implode(PHP_EOL, [
                'use First\Alpha;',
                'use Second\Example as ExampleAlias;',
            ]),
            self::buildUseStatements(
                'Example\Models',
                [
                    'Second\Example',
                    'Example\Models\LocalModel',
                    'First\Alpha',
                    'Second\Example',
                ],
                ['Second\Example' => 'ExampleAlias']
            )
        );
    }

    public function testCollision(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Import name `Example` collides');

        self::buildUseStatements('App', [
            'First\Example',
            'Second\Example',
        ]);
    }
}
