<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ConfigTest extends TestCase
{
    protected Config $config;

    public function testAddPath(): void
    {
        $this->assertSame(
            $this->config,
            $this->config->addPath('tests/config/dir1')
        );

        $this->assertSame(
            $this->config,
            $this->config->load('config')
        );

        $this->assertSame(
            'Value 1',
            $this->config->get('value')
        );
    }

    public function testAddPathDuplicate(): void
    {
        $this->config->addPath('tests/config/dir1');
        $this->config->addPath('tests/config/dir2');
        $this->config->addPath('tests/config/dir1');

        $this->assertSame(
            [
                Path::resolve('tests/config/dir1'),
                Path::resolve('tests/config/dir2'),
            ],
            $this->config->getPaths()
        );
    }

    public function testAddPathPrepend(): void
    {
        $this->config->addPath('tests/config/dir1');
        $this->config->addPath('tests/config/dir2', true);
        $this->config->load('config');

        $this->assertSame(
            'Value 1',
            $this->config->get('value')
        );
    }

    public function testAddPathPrependDuplicate(): void
    {
        $this->config->addPath('tests/config/dir1');
        $this->config->addPath('tests/config/dir2');
        $this->config->addPath('tests/config/dir2', true);

        $this->assertSame(
            [
                Path::resolve('tests/config/dir1'),
                Path::resolve('tests/config/dir2'),
            ],
            $this->config->getPaths()
        );
    }

    public function testAddPaths(): void
    {
        $this->config->addPath('tests/config/dir1');
        $this->config->addPath('tests/config/dir2');
        $this->config->load('config');

        $this->assertSame(
            'Value 2',
            $this->config->get('value')
        );
    }

    public function testConsume(): void
    {
        $this->config->set('test', 'Test');

        $this->assertSame(
            'Test',
            $this->config->consume('test')
        );

        $this->assertFalse(
            $this->config->has('test')
        );
    }

    public function testConsumeDeep(): void
    {
        $this->config->set('test.deep', 'Test');

        $this->assertSame(
            'Test',
            $this->config->consume('test.deep')
        );

        $this->assertFalse(
            $this->config->has('test.deep')
        );
    }

    public function testConsumeDefault(): void
    {
        $this->assertSame(
            'Test',
            $this->config->consume('test', 'Test')
        );
    }

    public function testConsumeInvalid(): void
    {
        $this->assertNull(
            $this->config->consume('test')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Config::class)
        );
    }

    public function testDelete(): void
    {
        $this->config->set('test', 'Test');

        $this->assertSame(
            $this->config,
            $this->config->delete('test')
        );

        $this->assertNull(
            $this->config->get('test')
        );
    }

    public function testDeleteDeep(): void
    {
        $this->config->set('test.deep', 'Test');

        $this->config->delete('test.deep');

        $this->assertNull(
            $this->config->get('test.deep')
        );
    }

    public function testDeleteInvalid(): void
    {
        $this->assertSame(
            $this->config,
            $this->config->delete('test')
        );
    }

    public function testGetDeep(): void
    {
        $this->config->set('test.deep', 'Test');

        $this->assertSame(
            'Test',
            $this->config->get('test.deep')
        );
    }

    public function testGetDefault(): void
    {
        $this->assertSame(
            'Test',
            $this->config->get('test', 'Test')
        );
    }

    public function testGetInvalid(): void
    {
        $this->assertNull(
            $this->config->get('test')
        );
    }

    public function testHas(): void
    {
        $this->config->set('test', 'Test');

        $this->assertTrue(
            $this->config->has('test')
        );
    }

    public function testHasInvalid(): void
    {
        $this->assertFalse(
            $this->config->has('test')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Config::class)
        );
    }

    public function testRemovePath(): void
    {
        $this->config->addPath('tests/config/dir1');

        $this->assertSame(
            $this->config,
            $this->config->removePath('tests/config/dir1')
        );

        $this->assertEmpty(
            $this->config->getPaths()
        );
    }

    public function testRemovePathInvalid(): void
    {
        $this->assertSame(
            $this->config,
            $this->config->removePath('tests/config/dir1')
        );
    }

    public function testSet(): void
    {
        $this->assertSame(
            $this->config,
            $this->config->set('test', 'Test')
        );

        $this->assertSame(
            'Test',
            $this->config->get('test')
        );
    }

    public function testSetDeep(): void
    {
        $this->config->set('test.deep', 'Test');

        $this->assertSame(
            [
                'deep' => 'Test',
            ],
            $this->config->get('test')
        );
    }

    public function testSetOverwrite(): void
    {
        $this->config->set('test', 'Test 1');
        $this->config->set('test', 'Test 2', false);

        $this->assertSame(
            'Test 1',
            $this->config->get('test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->config = new Config();
    }
}
