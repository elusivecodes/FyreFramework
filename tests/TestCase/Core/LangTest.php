<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Config;
use Fyre\Core\Lang;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class LangTest extends TestCase
{
    protected Lang $lang;

    public function testAddPath(): void
    {
        $this->assertSame(
            $this->lang,
            $this->lang->addPath('tests/lang/dir1')
        );

        $this->assertSame(
            'Value',
            $this->lang->get('test.value')
        );
    }

    public function testAddPathDuplicate(): void
    {
        $this->lang->addPath('tests/lang/dir1');
        $this->lang->addPath('tests/lang/dir2');
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            [
                Path::resolve('tests/lang/dir1'),
                Path::resolve('tests/lang/dir2'),
            ],
            $this->lang->getPaths()
        );
    }

    public function testAddPathPrependDuplicate(): void
    {
        $this->lang->addPath('tests/lang/dir1');
        $this->lang->addPath('tests/lang/dir2');
        $this->lang->addPath('tests/lang/dir2', true);

        $this->assertSame(
            [
                Path::resolve('tests/lang/dir1'),
                Path::resolve('tests/lang/dir2'),
            ],
            $this->lang->getPaths()
        );
    }

    public function testAddPaths(): void
    {
        $this->lang->addPath('tests/lang/dir1');
        $this->lang->addPath('tests/lang/dir2');

        $this->assertSame(
            'Alternate',
            $this->lang->get('test.value')
        );
    }

    public function testAddPathsWithPrepend(): void
    {
        $this->lang->addPath('tests/lang/dir1');
        $this->lang->addPath('tests/lang/dir2', true);

        $this->assertSame(
            'Value',
            $this->lang->get('test.value')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Lang::class)
        );
    }

    public function testGet(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            'Value',
            $this->lang->get('test.value')
        );
    }

    public function testGetArray(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            [
                'val1' => 'Value 1',
                'val2' => 'Value 2',
                'val3' => 'Value 3',
            ],
            $this->lang->get('test.data')
        );
    }

    public function testGetDeep(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            'Value 1',
            $this->lang->get('test.data.val1')
        );
    }

    public function testGetDefaultLocale(): void
    {
        $this->assertSame(
            $this->lang,
            $this->lang->setDefaultLocale('ru')
        );

        $this->assertSame(
            'ru',
            $this->lang->getDefaultLocale()
        );
    }

    public function testGetInvalid(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertNull(
            $this->lang->get('test.invalid')
        );
    }

    public function testGetLocale(): void
    {
        $this->assertSame(
            $this->lang,
            $this->lang->setLocale('ru')
        );

        $this->assertSame(
            'ru',
            $this->lang->getLocale()
        );
    }

    public function testGetLocaleCountry(): void
    {
        $this->lang->setLocale('en_au');
        $this->lang->addPath('tests/lang/dir2');

        $this->assertSame(
            'Localized',
            $this->lang->get('test.value')
        );
    }

    public function testGetLocaleCountryCase(): void
    {
        $this->lang->setLocale('en_AU');
        $this->lang->addPath('tests/lang/dir2');

        $this->assertSame(
            'Localized',
            $this->lang->get('test.value')
        );
    }

    public function testGetLocaleCountryExtended(): void
    {
        $this->lang->setLocale('en_au_posix');
        $this->lang->addPath('tests/lang/dir2');

        $this->assertSame(
            'Localized',
            $this->lang->get('test.value')
        );
    }

    public function testGetLocaleDefault(): void
    {
        $this->assertSame(
            'en',
            $this->lang->getLocale()
        );
    }

    public function testGetLocaleFallback(): void
    {
        $this->lang->setLocale('ru');
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            'Fallback',
            $this->lang->get('test.fallback')
        );
    }

    public function testGetPathFallback(): void
    {
        $this->lang->addPath('tests/lang/dir1');
        $this->lang->addPath('tests/lang/dir2');

        $this->assertSame(
            'Fallback',
            $this->lang->get('test.fallback')
        );
    }

    public function testGetWithData(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            'This is a test',
            $this->lang->get('test.message', ['test'])
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Lang::class)
        );
    }

    public function testRemovePath(): void
    {
        $this->lang->addPath('tests/lang/dir1');

        $this->assertSame(
            $this->lang,
            $this->lang->removePath('tests/lang/dir1')
        );

        $this->assertEmpty(
            $this->lang->getPaths()
        );
    }

    public function testRemovePathInvalid(): void
    {
        $this->assertSame(
            $this->lang,
            $this->lang->removePath('tests/lang/dir1')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $config = new Config();
        $config->set('App.defaultLocale', 'en');

        $this->lang = new Lang($config);
    }
}
